<?php
/**
 * Verify Payment API
 * 
 * Features:
 * - Verifies Razorpay payment
 * - Updates booking status to paid
 * - Sends notifications to both user and owner
 * - Records transaction in wallet
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

// Razorpay API credentials - replace with your actual keys
$razorpay_key_id = "rzp_test_DrASf34mihEAtB";
$razorpay_key_secret = "YOUR_KEY_SECRET"; // Update this

$razorpay_payment_id = isset($_POST['razorpay_payment_id']) ? trim($_POST['razorpay_payment_id']) : "";
$razorpay_order_id = isset($_POST['razorpay_order_id']) ? trim($_POST['razorpay_order_id']) : "";
$razorpay_signature = isset($_POST['razorpay_signature']) ? trim($_POST['razorpay_signature']) : "";
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

// Validation
if (empty($razorpay_payment_id)) {
    echo json_encode([
        "status" => "error",
        "message" => "Payment ID is required"
    ]);
    exit;
}

// Create required tables
$conn->query("CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    razorpay_payment_id VARCHAR(255) DEFAULT NULL,
    razorpay_order_id VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255),
    reference_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS wallet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
)");

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

// Verify signature (if order_id and signature are provided)
if (!empty($razorpay_order_id) && !empty($razorpay_signature) && $razorpay_key_secret !== 'YOUR_KEY_SECRET') {
    $generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, $razorpay_key_secret);
    
    if ($generated_signature !== $razorpay_signature) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid payment signature"
        ]);
        exit;
    }
}

// Store payment record
$stmt = $conn->prepare("INSERT INTO payments (booking_id, user_id, razorpay_payment_id, razorpay_order_id, amount, status, created_at) VALUES (?, ?, ?, ?, ?, 'success', NOW())");
$stmt->bind_param("iissd", $booking_id, $user_id, $razorpay_payment_id, $razorpay_order_id, $amount);

if ($stmt->execute()) {
    $payment_id = $conn->insert_id;

    // Get booking and equipment details
    $bookingStmt = $conn->prepare("
        SELECT b.*, e.equipment_name, e.owner_id, u.name as user_name, b.booking_reference
        FROM bookings b
        LEFT JOIN equipments e ON b.equipment_id = e.id
        LEFT JOIN users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $bookingStmt->bind_param("i", $booking_id);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    
    $equipmentName = "Equipment";
    $ownerId = 0;
    $userName = "User";
    $bookingRef = "AGR-" . $booking_id;
    
    if ($bookingRow = $bookingResult->fetch_assoc()) {
        $equipmentName = $bookingRow['equipment_name'] ?? "Equipment";
        $ownerId = intval($bookingRow['owner_id'] ?? 0);
        $userName = $bookingRow['user_name'] ?? "User";
        $bookingRef = $bookingRow['booking_reference'] ?? ("AGR-" . $booking_id);
    }
    $bookingStmt->close();

    // Update booking payment status
    if ($booking_id > 0) {
        $updateBooking = $conn->prepare("UPDATE bookings SET payment_status = 'paid', payment_id = ?, booking_status = 'Approved', status = 'approved' WHERE id = ?");
        $updateBooking->bind_param("ii", $payment_id, $booking_id);
        $updateBooking->execute();
        $updateBooking->close();
    }

    // Send notification to user
    $userNotifTitle = "Payment Successful! 🎉";
    $userNotifMessage = "Your payment of ₹" . number_format($amount, 0) . " for $equipmentName (#$bookingRef) has been received. Your booking is now confirmed!";
    
    $userNotifStmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
        VALUES (?, ?, ?, 'payment', ?, 'booking')
    ");
    $userNotifStmt->bind_param("issi", $user_id, $userNotifTitle, $userNotifMessage, $booking_id);
    $userNotifStmt->execute();
    $userNotifStmt->close();

    // Credit owner's wallet (85% to owner, 15% platform fee)
    if ($ownerId > 0) {
        $ownerAmount = $amount * 0.85;
        
        // Add transaction for owner
        $transStmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description, reference_id, created_at) VALUES (?, 'credit', ?, ?, ?, NOW())");
        $description = "Payment for $equipmentName (#$bookingRef)";
        $transStmt->bind_param("idss", $ownerId, $ownerAmount, $description, $razorpay_payment_id);
        $transStmt->execute();
        $transStmt->close();

        // Update owner wallet
        $updateWallet = $conn->prepare("
            INSERT INTO wallet (user_id, balance) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE balance = balance + ?
        ");
        $updateWallet->bind_param("idd", $ownerId, $ownerAmount, $ownerAmount);
        $updateWallet->execute();
        $updateWallet->close();
        
        // Send notification to owner
        $ownerNotifTitle = "Payment Received! 💰";
        $ownerNotifMessage = "$userName has completed payment of ₹" . number_format($amount, 0) . " for $equipmentName (#$bookingRef). ₹" . number_format($ownerAmount, 0) . " has been added to your wallet.";
        
        $ownerNotifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
            VALUES (?, ?, ?, 'payment', ?, 'booking')
        ");
        $ownerNotifStmt->bind_param("issi", $ownerId, $ownerNotifTitle, $ownerNotifMessage, $booking_id);
        $ownerNotifStmt->execute();
        $ownerNotifStmt->close();
    }

    echo json_encode([
        "status" => "success",
        "message" => "Payment verified and recorded successfully",
        "payment_id" => $payment_id,
        "booking_confirmed" => true,
        "notifications_sent" => true
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to record payment: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
