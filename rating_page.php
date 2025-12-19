<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php"; // DB connection

// Read form-data values
$user_id      = $_POST["user_id"] ?? "";
$equipment_id = $_POST["equipment_id"] ?? "";
$operator_id  = $_POST["operator_id"] ?? "";
$rating       = $_POST["rating"] ?? "";
$comment      = $_POST["comment"] ?? "";

// Validation
if ($user_id == "" || $equipment_id == "" || $operator_id == "" || $rating == "") {
    echo json_encode([
        "status" => "error",
        "message" => "user_id, equipment_id, operator_id and rating are required"
    ]);
    exit;
}

// Insert Query
$sql = "INSERT INTO ratings (user_id, equipment_id, operator_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiis", $user_id, $equipment_id, $operator_id, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Rating submitted successfully",
        "rating_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database insert failed: " . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
