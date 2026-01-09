<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$equipment_id = isset($_POST['equipment_id']) ? intval($_POST['equipment_id']) : 0;
$operator_id = isset($_POST['operator_id']) ? intval($_POST['operator_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : "";

// Validation
if ($user_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode([
        "status" => "error",
        "message" => "Rating must be between 1 and 5"
    ]);
    exit;
}

// Insert rating
$stmt = $conn->prepare("INSERT INTO ratings (user_id, equipment_id, operator_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iiiis", $user_id, $equipment_id, $operator_id, $rating, $comment);

if ($stmt->execute()) {
    // Update equipment average rating
    if ($equipment_id > 0) {
        $updateEquipment = $conn->prepare("
            UPDATE equipments 
            SET rating = (
                SELECT AVG(rating) FROM ratings WHERE equipment_id = ?
            )
            WHERE id = ?
        ");
        $updateEquipment->bind_param("ii", $equipment_id, $equipment_id);
        $updateEquipment->execute();
        $updateEquipment->close();
    }

    // Update operator average rating
    if ($operator_id > 0) {
        $updateOperator = $conn->prepare("
            UPDATE operators 
            SET rating = (
                SELECT AVG(rating) FROM ratings WHERE operator_id = ?
            )
            WHERE id = ?
        ");
        $updateOperator->bind_param("ii", $operator_id, $operator_id);
        $updateOperator->execute();
        $updateOperator->close();
    }

    echo json_encode([
        "status" => "success",
        "message" => "Rating submitted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to submit rating"
    ]);
}

$stmt->close();
$conn->close();
?>
