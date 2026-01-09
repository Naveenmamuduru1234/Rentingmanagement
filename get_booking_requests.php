<?php
/**
 * Get Booking Requests API
 * 
 * Returns all booking requests for an equipment owner
 * with proper status handling and payment info
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'db.php';

$owner_id = $_GET['owner_id'] ?? -1;

if ($owner_id == -1) {
    echo json_encode([
        "status" => "error",
        "message" => "Owner ID is required"
    ]);
    exit;
}

// Get booking requests for owner's equipment
$query = "SELECT 
            b.id,
            u.name as user_name,
            u.mobile as user_phone,
            e.equipment_name as equipment_name,
            e.id as equipment_id,
            DATE_FORMAT(b.start_date, '%b %d, %Y') as date_time,
            DATEDIFF(b.end_date, b.start_date) + 1 as days,
            COALESCE(b.location, 'Location not specified') as location,
            COALESCE(b.total_price, 0) as earnings,
            COALESCE(b.booking_status, b.status, 'Pending') as status,
            b.payment_status,
            b.booking_reference,
            b.created_at
          FROM bookings b
          INNER JOIN equipments e ON b.equipment_id = e.id
          INNER JOIN users u ON b.user_id = u.id
          WHERE e.owner_id = ?
          ORDER BY 
            CASE COALESCE(b.booking_status, b.status)
                WHEN 'Pending' THEN 1 
                WHEN 'pending' THEN 1
                WHEN 'Approved' THEN 2 
                WHEN 'approved' THEN 2
                ELSE 3 
            END,
            b.created_at DESC
          LIMIT 50";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database query error: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $days = intval($row['days']);
    $duration = $days > 1 ? "$days days" : "$days day";
    
    $requests[] = [
        "id" => (int) $row['id'],
        "user_name" => $row['user_name'],
        "user_phone" => $row['user_phone'],
        "equipment_name" => $row['equipment_name'],
        "equipment_id" => (int) $row['equipment_id'],
        "date_time" => $row['date_time'],
        "duration" => $duration,
        "location" => $row['location'],
        "earnings" => (float) $row['earnings'],
        "status" => ucfirst(strtolower($row['status'])),
        "payment_status" => $row['payment_status'] ?? 'pending',
        "booking_reference" => $row['booking_reference']
    ];
}

// Get counts for stats
$pendingCount = 0;
$approvedCount = 0;
$totalEarnings = 0;

foreach ($requests as $req) {
    if (strtolower($req['status']) == 'pending') {
        $pendingCount++;
    }
    if (strtolower($req['status']) == 'approved') {
        $approvedCount++;
        if (strtolower($req['payment_status']) == 'paid') {
            $totalEarnings += $req['earnings'];
        }
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Booking requests retrieved successfully",
    "data" => $requests,
    "stats" => [
        "pending_count" => $pendingCount,
        "approved_count" => $approvedCount,
        "total_earnings" => $totalEarnings
    ]
]);

$stmt->close();
$conn->close();
?>
