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

// Get wallet balance
$walletQuery = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
$walletQuery->bind_param("i", $user_id);
$walletQuery->execute();
$walletResult = $walletQuery->get_result();

$balance = 0;
if ($walletResult->num_rows > 0) {
    $walletRow = $walletResult->fetch_assoc();
    $balance = floatval($walletRow['balance']);
}

echo json_encode([
    "status" => "success",
    "data" => [
        "balance" => $balance,
        "currency" => "INR"
    ]
]);

$walletQuery->close();
$conn->close();
?>
