<?php
/**
 * Update Booking Status API
 * 
 * Features:
 * - Update booking status (approve/decline)
 * - Send automatic notifications to user
 * - Trigger payment flow when approved
 * - Update equipment availability
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require "db.php";

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : "";

if ($booking_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Booking ID is required"
    ]);
    exit;
}

if (!in_array($status, ['approved', 'declined', 'cancelled', 'completed'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid status. Must be: approved, declined, cancelled, or completed"
    ]);
    exit;
}

// Get booking details first
$bookingStmt = $conn->prepare("
    SELECT b.*, e.equipment_name, e.price_per_hour, e.owner_id, 
           u.name as user_name, u.email as user_email
    FROM bookings b
    LEFT JOIN equipments e ON b.equipment_id = e.id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.id = ?
");
$bookingStmt->bind_param("i", $booking_id);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();

if ($bookingResult->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Booking not found"
    ]);
    exit;
}

$booking = $bookingResult->fetch_assoc();
$bookingStmt->close();

$userId = intval($booking['user_id']);
$equipmentName = $booking['equipment_name'] ?? "Equipment";
$totalPrice = $booking['total_price'] ?? "0";
$bookingRef = $booking['booking_reference'] ?? "AGR-" . $booking_id;

// Update booking status
$displayStatus = ucfirst($status);
$updateStmt = $conn->prepare("
    UPDATE bookings 
    SET booking_status = ?, status = ?, updated_at = NOW()
    WHERE id = ?
");
$updateStmt->bind_param("ssi", $displayStatus, $status, $booking_id);

if (!$updateStmt->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update booking status"
    ]);
    exit;
}
$updateStmt->close();

// Create notifications table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    is_read TINYINT DEFAULT 0,
    reference_id INT DEFAULT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create payments table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    razorpay_payment_id VARCHAR(255) DEFAULT NULL,
    razorpay_order_id VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id)
)");

// Send notification based on status
$notifTitle = "";
$notifMessage = "";
$notifType = "general";

switch ($status) {
    case 'approved':
        $notifTitle = "Booking Approved! 🎉";
        $notifMessage = "Great news! Your booking for $equipmentName (#$bookingRef) has been approved. Please complete the payment of ₹$totalPrice to confirm your booking.";
        $notifType = "booking_confirmed";
        
        // Create pending payment record
        $paymentStmt = $conn->prepare("
            INSERT INTO payments (booking_id, user_id, amount, status)
            VALUES (?, ?, ?, 'pending')
            ON DUPLICATE KEY UPDATE status = 'pending', amount = ?
        ");
        $priceFloat = floatval($totalPrice);
        $paymentStmt->bind_param("iidd", $booking_id, $userId, $priceFloat, $priceFloat);
        $paymentStmt->execute();
        $paymentStmt->close();
        
        // Update booking to show payment pending
        $conn->query("UPDATE bookings SET payment_status = 'pending' WHERE id = $booking_id");
        break;
        
    case 'declined':
        $notifTitle = "Booking Request Declined";
        $notifMessage = "Unfortunately, your booking request for $equipmentName (#$bookingRef) has been declined by the owner. You can try booking for different dates or browse other equipment.";
        $notifType = "booking_cancelled";
        break;
        
    case 'cancelled':
        $notifTitle = "Booking Cancelled";
        $notifMessage = "Your booking for $equipmentName (#$bookingRef) has been cancelled.";
        $notifType = "booking_cancelled";
        break;
        
    case 'completed':
        $notifTitle = "Booking Completed! 🌟";
        $notifMessage = "Your rental of $equipmentName (#$bookingRef) is now complete. We hope you had a great experience! Please leave a review to help other farmers.";
        $notifType = "review_request";
        break;
}

// Insert notification
$notifStmt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
    VALUES (?, ?, ?, ?, ?, 'booking')
");
$notifStmt->bind_param("isssi", $userId, $notifTitle, $notifMessage, $notifType, $booking_id);
$notifStmt->execute();
$notifStmt->close();

// If approved, also notify owner confirmation
if ($status === 'approved') {
    $ownerId = intval($booking['owner_id'] ?? 0);
    if ($ownerId > 0) {
        $ownerNotifTitle = "Booking Confirmed!";
        $ownerNotifMessage = "You have approved the booking for $equipmentName (#$bookingRef). The user has been notified to complete payment.";
        
        $ownerNotifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
            VALUES (?, ?, ?, 'booking_confirmed', ?, 'booking')
        ");
        $ownerNotifStmt->bind_param("issi", $ownerId, $ownerNotifTitle, $ownerNotifMessage, $booking_id);
        $ownerNotifStmt->execute();
        $ownerNotifStmt->close();
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Booking status updated to " . $displayStatus,
    "notification_sent" => true,
    "booking_id" => $booking_id,
    "new_status" => $displayStatus,
    "payment_required" => ($status === 'approved'),
    "amount" => ($status === 'approved') ? floatval($totalPrice) : null
]);

$conn->close();
?>
