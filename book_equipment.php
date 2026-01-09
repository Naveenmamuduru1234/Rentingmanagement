<?php
/**
 * Enhanced Book Equipment API
 * 
 * Features:
 * - AI-powered availability check before booking
 * - Automatic notification to equipment owner
 * - Payment tracking setup
 * - Booking reference generation
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

// Get input parameters
$equipment_id = $_POST["equipment_id"] ?? "";
$user_id      = $_POST["user_id"] ?? "";
$start_date   = $_POST["start_date"] ?? "";
$end_date     = $_POST["end_date"] ?? "";
$total_price  = $_POST["total_price"] ?? "";
$operator_id  = isset($_POST["operator_id"]) && $_POST["operator_id"] != "" ? intval($_POST["operator_id"]) : null;
$location     = $_POST["location"] ?? "";

// Required validation
if ($equipment_id == "" || $user_id == "" || $start_date == "" || $end_date == "") {
    echo json_encode([
        "status" => "error",
        "message" => "equipment_id, user_id, start_date, end_date are required"
    ]);
    exit;
}

// Create required tables if not exist
$conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    user_id INT NOT NULL,
    operator_id INT DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price VARCHAR(50),
    location VARCHAR(255) DEFAULT NULL,
    booking_status ENUM('Pending', 'Approved', 'Declined', 'Completed', 'Cancelled') DEFAULT 'Pending',
    status VARCHAR(50) DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_id INT DEFAULT NULL,
    booking_reference VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (booking_status)
)");

$conn->query("CREATE TABLE IF NOT EXISTS notifications (
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
    INDEX idx_is_read (is_read)
)");

// Convert datetime to date format for the database
$start_date_only = date('Y-m-d', strtotime($start_date));
$end_date_only = date('Y-m-d', strtotime($end_date));

// AI Availability Check - Check for existing bookings that conflict
$conflictCheck = $conn->prepare("
    SELECT COUNT(*) as conflict_count 
    FROM bookings 
    WHERE equipment_id = ? 
    AND (booking_status IN ('Pending', 'Approved') OR status IN ('pending', 'approved'))
    AND NOT (end_date < ? OR start_date > ?)
");
$conflictCheck->bind_param("iss", $equipment_id, $start_date_only, $end_date_only);
$conflictCheck->execute();
$conflictResult = $conflictCheck->get_result();
$conflictRow = $conflictResult->fetch_assoc();
$conflictCheck->close();

if ($conflictRow['conflict_count'] > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "This equipment is already booked for the selected dates. Please choose different dates.",
        "ai_message" => "Our AI detected a booking conflict. The equipment is not available for your selected time slot."
    ]);
    exit;
}

// Generate booking reference
$booking_reference = "AGR-" . date('Y') . "-" . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Convert total_price to string
$total_price_str = strval($total_price);

// Insert booking
if ($operator_id !== null) {
    $stmt = $conn->prepare("
        INSERT INTO bookings (equipment_id, user_id, operator_id, start_date, end_date, total_price, location, booking_status, status, booking_reference)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 'pending', ?)
    ");
    $stmt->bind_param("iiiissss", $equipment_id, $user_id, $operator_id, $start_date_only, $end_date_only, $total_price_str, $location, $booking_reference);
} else {
    $stmt = $conn->prepare("
        INSERT INTO bookings (equipment_id, user_id, start_date, end_date, total_price, location, booking_status, status, booking_reference)
        VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'pending', ?)
    ");
    $stmt->bind_param("iisssss", $equipment_id, $user_id, $start_date_only, $end_date_only, $total_price_str, $location, $booking_reference);
}

if ($stmt->execute()) {
    $booking_id = $stmt->insert_id;
    
    // Get equipment and owner details for notification
    $eqStmt = $conn->prepare("
        SELECT e.equipment_name, e.owner_id, u.name as user_name
        FROM equipments e
        LEFT JOIN users u ON u.id = ?
        WHERE e.id = ?
    ");
    $eqStmt->bind_param("ii", $user_id, $equipment_id);
    $eqStmt->execute();
    $eqResult = $eqStmt->get_result();
    
    $equipmentName = "Equipment";
    $userName = "User";
    $ownerId = 0;
    
    if ($eqRow = $eqResult->fetch_assoc()) {
        $equipmentName = $eqRow['equipment_name'] ?? "Equipment";
        $userName = $eqRow['user_name'] ?? "User";
        $ownerId = intval($eqRow['owner_id'] ?? 0);
    }
    $eqStmt->close();
    
    // Send notification to equipment owner
    if ($ownerId > 0) {
        $notifTitle = "New Booking Request!";
        $notifMessage = "$userName has requested to book your $equipmentName from " . date('M d', strtotime($start_date_only)) . " to " . date('M d', strtotime($end_date_only)) . ". Total: ₹$total_price_str";
        
        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
            VALUES (?, ?, ?, 'booking_request', ?, 'booking')
        ");
        $notifStmt->bind_param("issi", $ownerId, $notifTitle, $notifMessage, $booking_id);
        $notifStmt->execute();
        $notifStmt->close();
    }
    
    // Send confirmation notification to user
    $userNotifTitle = "Booking Request Submitted!";
    $userNotifMessage = "Your booking request for $equipmentName (#$booking_reference) has been submitted. You'll be notified once the owner approves.";
    
    $userNotifStmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type)
        VALUES (?, ?, ?, 'booking_confirmed', ?, 'booking')
    ");
    $userNotifStmt->bind_param("issi", $user_id, $userNotifTitle, $userNotifMessage, $booking_id);
    $userNotifStmt->execute();
    $userNotifStmt->close();
    
    echo json_encode([
        "status" => "success",
        "message" => "Booking request submitted successfully!",
        "booking_id" => $booking_id,
        "booking_reference" => $booking_reference,
        "ai_message" => "Your booking request has been sent to the equipment owner. You'll receive a notification once they approve your request.",
        "next_step" => "wait_for_approval"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Booking failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
