<?php
/**
 * AI-Powered Availability Checker
 * 
 * This endpoint checks equipment availability in real-time based on:
 * - Current bookings
 * - Date/time conflicts
 * - Equipment status
 * 
 * Returns availability status and suggestions
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$equipment_id = isset($_GET['equipment_id']) ? intval($_GET['equipment_id']) : 0;
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : "";
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : "";
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Default response
$response = [
    "status" => "success",
    "is_available" => false,
    "availability_status" => "unknown",
    "message" => "",
    "conflicts" => [],
    "suggestions" => [],
    "next_available_slot" => null,
    "ai_recommendation" => ""
];

if ($equipment_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Equipment ID is required"
    ]);
    exit;
}

// Get equipment details
$eqStmt = $conn->prepare("SELECT id, equipment_name, availability, price_per_hour FROM equipments WHERE id = ?");
$eqStmt->bind_param("i", $equipment_id);
$eqStmt->execute();
$eqResult = $eqStmt->get_result();

if ($eqResult->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Equipment not found"
    ]);
    exit;
}

$equipment = $eqResult->fetch_assoc();
$eqStmt->close();

// Check base availability status
$baseAvailability = strtolower($equipment['availability'] ?? 'available');

if ($baseAvailability === 'not available' || $baseAvailability === 'maintenance') {
    $response['is_available'] = false;
    $response['availability_status'] = 'not_available';
    $response['message'] = "This equipment is currently not available for booking";
    $response['ai_recommendation'] = "This equipment is marked as unavailable. Check back later or browse similar equipment.";
    echo json_encode($response);
    $conn->close();
    exit;
}

// If no dates provided, just return base availability
if (empty($start_date) || empty($end_date)) {
    // Get all current/upcoming bookings for this equipment
    $bookingsStmt = $conn->prepare("
        SELECT b.id, b.start_date, b.end_date, b.booking_status, b.status
        FROM bookings b
        WHERE b.equipment_id = ?
        AND (b.booking_status IN ('pending', 'approved', 'Pending', 'Approved') 
             OR b.status IN ('pending', 'approved', 'Pending', 'Approved'))
        AND b.end_date >= CURDATE()
        ORDER BY b.start_date ASC
        LIMIT 10
    ");
    $bookingsStmt->bind_param("i", $equipment_id);
    $bookingsStmt->execute();
    $bookingsResult = $bookingsStmt->get_result();
    
    $upcomingBookings = [];
    while ($booking = $bookingsResult->fetch_assoc()) {
        $upcomingBookings[] = [
            "start_date" => $booking['start_date'],
            "end_date" => $booking['end_date'],
            "status" => $booking['booking_status'] ?: $booking['status']
        ];
    }
    $bookingsStmt->close();
    
    if (count($upcomingBookings) == 0) {
        $response['is_available'] = true;
        $response['availability_status'] = 'available';
        $response['message'] = "Equipment available for booking";
        $response['ai_recommendation'] = "Great choice! This equipment has no upcoming bookings and is ready for rental.";
    } else {
        $response['is_available'] = true;
        $response['availability_status'] = 'partially_available';
        $response['message'] = "Equipment available, but has " . count($upcomingBookings) . " upcoming booking(s)";
        $response['conflicts'] = $upcomingBookings;
        $response['ai_recommendation'] = "This equipment is popular! It has " . count($upcomingBookings) . " upcoming booking(s). Select your dates carefully to avoid conflicts.";
    }
    
    echo json_encode($response);
    $conn->close();
    exit;
}

// Convert dates
$startDateFormatted = date('Y-m-d', strtotime($start_date));
$endDateFormatted = date('Y-m-d', strtotime($end_date));

// Check for booking conflicts
$conflictStmt = $conn->prepare("
    SELECT b.id, b.start_date, b.end_date, b.booking_status, b.status, u.name as booked_by
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.equipment_id = ?
    AND (b.booking_status IN ('pending', 'approved', 'Pending', 'Approved') 
         OR b.status IN ('pending', 'approved', 'Pending', 'Approved'))
    AND NOT (b.end_date < ? OR b.start_date > ?)
    ORDER BY b.start_date ASC
");
$conflictStmt->bind_param("iss", $equipment_id, $startDateFormatted, $endDateFormatted);
$conflictStmt->execute();
$conflictResult = $conflictStmt->get_result();

$conflicts = [];
while ($conflict = $conflictResult->fetch_assoc()) {
    $conflicts[] = [
        "booking_id" => $conflict['id'],
        "start_date" => $conflict['start_date'],
        "end_date" => $conflict['end_date'],
        "status" => $conflict['booking_status'] ?: $conflict['status'],
        "booked_by" => $conflict['booked_by']
    ];
}
$conflictStmt->close();

if (count($conflicts) > 0) {
    $response['is_available'] = false;
    $response['availability_status'] = 'conflict';
    $response['message'] = "Selected dates conflict with " . count($conflicts) . " existing booking(s)";
    $response['conflicts'] = $conflicts;
    
    // Find next available slot using AI logic
    $nextSlotStmt = $conn->prepare("
        SELECT DATE_ADD(MAX(end_date), INTERVAL 1 DAY) as next_available
        FROM bookings
        WHERE equipment_id = ?
        AND (booking_status IN ('pending', 'approved', 'Pending', 'Approved') 
             OR status IN ('pending', 'approved', 'Pending', 'Approved'))
        AND end_date >= ?
    ");
    $nextSlotStmt->bind_param("is", $equipment_id, $startDateFormatted);
    $nextSlotStmt->execute();
    $nextResult = $nextSlotStmt->get_result();
    
    if ($nextRow = $nextResult->fetch_assoc()) {
        $response['next_available_slot'] = $nextRow['next_available'];
        $response['suggestions'][] = [
            "type" => "next_available",
            "date" => $nextRow['next_available'],
            "message" => "Equipment available from " . date('M d, Y', strtotime($nextRow['next_available']))
        ];
    }
    $nextSlotStmt->close();
    
    // AI Recommendation
    $response['ai_recommendation'] = "The dates you selected are not available. " .
        "I found " . count($conflicts) . " conflicting booking(s). " .
        ($response['next_available_slot'] ? "The equipment will be available from " . date('M d, Y', strtotime($response['next_available_slot'])) . ". " : "") .
        "Would you like to book for a different date?";
    
} else {
    $response['is_available'] = true;
    $response['availability_status'] = 'available';
    $response['message'] = "Equipment is available for your selected dates!";
    $response['ai_recommendation'] = "Perfect! The " . $equipment['equipment_name'] . " is available for your selected dates. " .
        "The rental rate is ₹" . $equipment['price_per_hour'] . " per hour. Proceed to confirm your booking!";
}

// Add equipment info to response
$response['equipment'] = [
    "id" => intval($equipment['id']),
    "name" => $equipment['equipment_name'],
    "price_per_hour" => floatval($equipment['price_per_hour']),
    "base_availability" => $equipment['availability']
];

echo json_encode($response);

$conn->close();
?>
