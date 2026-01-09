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

// Create notifications table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking_confirmed', 'operator_assigned', 'payment', 'offer', 'review_request', 'general', 'app_update', 'booking_request', 'booking_cancelled') DEFAULT 'general',
    is_read TINYINT DEFAULT 0,
    reference_id INT DEFAULT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
)";
$conn->query($createTable);

// Get notifications for user
$sql = "SELECT 
            n.id,
            n.title,
            n.message,
            n.type,
            n.is_read,
            n.created_at,
            n.reference_id,
            n.reference_type
        FROM notifications n
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 50";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Calculate time ago
    $created = new DateTime($row['created_at']);
    $now = new DateTime();
    $diff = $now->diff($created);
    
    if ($diff->days > 0) {
        $timeAgo = $diff->days . " day" . ($diff->days > 1 ? "s" : "") . " ago";
    } elseif ($diff->h > 0) {
        $timeAgo = $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
    } elseif ($diff->i > 0) {
        $timeAgo = $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
    } else {
        $timeAgo = "Just now";
    }

    $notifications[] = [
        "id" => intval($row['id']),
        "title" => $row['title'],
        "message" => $row['message'],
        "type" => $row['type'],
        "is_read" => $row['is_read'] == 1,
        "time_ago" => $timeAgo,
        "created_at" => $row['created_at'],
        "reference_id" => $row['reference_id'] ? intval($row['reference_id']) : null,
        "reference_type" => $row['reference_type']
    ];
}

$stmt->close();

// Get unread count
$countSql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$unreadCount = intval($countRow['unread_count']);
$countStmt->close();

echo json_encode([
    "status" => "success",
    "unread_count" => $unreadCount,
    "data" => $notifications
]);

$conn->close();
?>
