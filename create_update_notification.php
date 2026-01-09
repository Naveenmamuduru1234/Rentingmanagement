<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

// This endpoint is for admins to create update notifications for all users
// or for the system to notify users about updates

$version = isset($_POST['version']) ? trim($_POST['version']) : "";
$update_title = isset($_POST['update_title']) ? trim($_POST['update_title']) : "App Update Available";
$update_message = isset($_POST['update_message']) ? trim($_POST['update_message']) : "";
$target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if (empty($update_message)) {
    $update_message = "A new version of Agro Rent is available! Update now to get the latest features and improvements.";
}

// First, update the notifications table type enum if needed
$alterTable = "ALTER TABLE notifications MODIFY COLUMN type ENUM('booking_confirmed', 'operator_assigned', 'payment', 'offer', 'review_request', 'general', 'app_update') DEFAULT 'general'";
$conn->query($alterTable);

// If target_user_id is 0, send to all users
if ($target_user_id > 0) {
    // Send to specific user
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, 'app_update', 0)");
    $stmt->bind_param("iss", $target_user_id, $update_title, $update_message);
    
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Update notification sent to user"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to send notification"
        ]);
    }
    $stmt->close();
} else {
    // Send to all users
    $usersResult = $conn->query("SELECT id FROM users");
    $successCount = 0;
    
    while ($user = $usersResult->fetch_assoc()) {
        $userId = $user['id'];
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, 'app_update', 0)");
        $stmt->bind_param("iss", $userId, $update_title, $update_message);
        if ($stmt->execute()) {
            $successCount++;
        }
        $stmt->close();
    }
    
    echo json_encode([
        "status" => "success",
        "message" => "Update notification sent to $successCount users"
    ]);
}

$conn->close();
?>
