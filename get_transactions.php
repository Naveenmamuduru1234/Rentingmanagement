<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

// Get transactions
$stmt = $conn->prepare("
    SELECT 
        id,
        user_id,
        type,
        amount,
        description,
        reference_id,
        created_at
    FROM transactions 
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        "id" => intval($row['id']),
        "user_id" => intval($row['user_id']),
        "type" => $row['type'],
        "amount" => floatval($row['amount']),
        "description" => $row['description'],
        "reference_id" => $row['reference_id'],
        "created_at" => $row['created_at']
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $transactions
]);

$stmt->close();
$conn->close();
?>
