<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
$mark_all = isset($_POST['mark_all']) ? $_POST['mark_all'] === 'true' : false;

if ($user_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

if ($mark_all) {
    // Mark all notifications as read
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
} else {
    if ($notification_id <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Notification ID is required"
        ]);
        exit;
    }
    
    // Mark single notification as read
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
}

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => $mark_all ? "All notifications marked as read" : "Notification marked as read"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update notification"
    ]);
}

$stmt->close();
$conn->close();
?>
