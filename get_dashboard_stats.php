<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'db.php';

$user_id = $_GET['user_id'] ?? -1;

if ($user_id == -1) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

// Get this month's revenue
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

$revenue_query = "SELECT COALESCE(SUM(total_price), 0) as this_month_revenue 
                  FROM bookings 
                  WHERE owner_id = ? 
                  AND status = 'completed' 
                  AND created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param("iss", $user_id, $month_start, $month_end);
$stmt->execute();
$revenue_result = $stmt->get_result();
$revenue_data = $revenue_result->fetch_assoc();
$this_month_revenue = $revenue_data['this_month_revenue'] ?? 0;

// Get total bookings count
$bookings_query = "SELECT COUNT(*) as total_bookings FROM bookings WHERE owner_id = ?";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$bookings_data = $bookings_result->fetch_assoc();
$total_bookings = $bookings_data['total_bookings'] ?? 0;

// Get total equipment count
$equipment_query = "SELECT COUNT(*) as total_equipment FROM equipment WHERE owner_id = ?";
$stmt = $conn->prepare($equipment_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$equipment_result = $stmt->get_result();
$equipment_data = $equipment_result->fetch_assoc();
$total_equipment = $equipment_data['total_equipment'] ?? 0;

// Get average rating
$rating_query = "SELECT COALESCE(AVG(r.rating), 0) as average_rating 
                 FROM ratings r 
                 INNER JOIN equipment e ON r.equipment_id = e.id 
                 WHERE e.owner_id = ?";
$stmt = $conn->prepare($rating_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rating_result = $stmt->get_result();
$rating_data = $rating_result->fetch_assoc();
$average_rating = round($rating_data['average_rating'] ?? 0, 1);

// Get pending requests count
$pending_query = "SELECT COUNT(*) as pending_requests 
                  FROM bookings 
                  WHERE owner_id = ? AND status = 'pending'";
$stmt = $conn->prepare($pending_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_result = $stmt->get_result();
$pending_data = $pending_result->fetch_assoc();
$pending_requests = $pending_data['pending_requests'] ?? 0;

// Get active rentals count
$active_query = "SELECT COUNT(*) as active_rentals 
                 FROM bookings 
                 WHERE owner_id = ? AND status = 'in_progress'";
$stmt = $conn->prepare($active_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_result = $stmt->get_result();
$active_data = $active_result->fetch_assoc();
$active_rentals = $active_data['active_rentals'] ?? 0;

echo json_encode([
    "status" => "success",
    "message" => "Dashboard stats retrieved successfully",
    "data" => [
        "this_month_revenue" => (float) $this_month_revenue,
        "total_bookings" => (int) $total_bookings,
        "total_equipment" => (int) $total_equipment,
        "average_rating" => (float) $average_rating,
        "pending_requests" => (int) $pending_requests,
        "active_rentals" => (int) $active_rentals
    ]
]);

$stmt->close();
$conn->close();
?>
