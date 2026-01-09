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

// Get total revenue from bookings
$revenueQuery = $conn->prepare("SELECT COALESCE(SUM(total_price), 0) as total_revenue FROM bookings WHERE status='completed'");
$revenueQuery->execute();
$revenueResult = $revenueQuery->get_result();
$revenueRow = $revenueResult->fetch_assoc();
$totalRevenue = floatval($revenueRow['total_revenue']);

// Get total bookings count
$bookingsQuery = $conn->prepare("SELECT COUNT(*) as total_bookings FROM bookings");
$bookingsQuery->execute();
$bookingsResult = $bookingsQuery->get_result();
$bookingsRow = $bookingsResult->fetch_assoc();
$totalBookings = intval($bookingsRow['total_bookings']);

// Get total equipment count
$equipmentQuery = $conn->prepare("SELECT COUNT(*) as total_equipment FROM equipments");
$equipmentQuery->execute();
$equipmentResult = $equipmentQuery->get_result();
$equipmentRow = $equipmentResult->fetch_assoc();
$totalEquipment = intval($equipmentRow['total_equipment']);

// Get average rating
$ratingQuery = $conn->prepare("SELECT COALESCE(AVG(rating), 0) as average_rating FROM ratings");
$ratingQuery->execute();
$ratingResult = $ratingQuery->get_result();
$ratingRow = $ratingResult->fetch_assoc();
$averageRating = round(floatval($ratingRow['average_rating']), 1);

// Get monthly revenue (last 6 months)
$monthlyRevenueQuery = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_price) as revenue
    FROM bookings 
    WHERE status='completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$monthlyRevenueQuery->execute();
$monthlyRevenueResult = $monthlyRevenueQuery->get_result();
$monthlyRevenue = [];
while ($row = $monthlyRevenueResult->fetch_assoc()) {
    $monthlyRevenue[] = [
        "month" => $row['month'],
        "revenue" => floatval($row['revenue'])
    ];
}

// Get top equipment by bookings
$topEquipmentQuery = $conn->prepare("
    SELECT 
        e.id as equipment_id,
        e.name as equipment_name,
        COUNT(b.id) as total_bookings,
        COALESCE(SUM(b.total_price), 0) as revenue
    FROM equipments e
    LEFT JOIN bookings b ON e.id = b.equipment_id
    GROUP BY e.id, e.name
    ORDER BY total_bookings DESC
    LIMIT 5
");
$topEquipmentQuery->execute();
$topEquipmentResult = $topEquipmentQuery->get_result();
$topEquipment = [];
while ($row = $topEquipmentResult->fetch_assoc()) {
    $topEquipment[] = [
        "equipment_id" => intval($row['equipment_id']),
        "equipment_name" => $row['equipment_name'],
        "total_bookings" => intval($row['total_bookings']),
        "revenue" => floatval($row['revenue'])
    ];
}

echo json_encode([
    "status" => "success",
    "data" => [
        "total_revenue" => $totalRevenue,
        "total_bookings" => $totalBookings,
        "total_equipment" => $totalEquipment,
        "average_rating" => $averageRating,
        "monthly_revenue" => $monthlyRevenue,
        "top_equipment" => $topEquipment
    ]
]);

$conn->close();
?>
