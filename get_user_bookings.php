<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Get user_id from query parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid user ID"
    ]);
    exit;
}

// Fetch all bookings for this user with equipment details
$sql = "SELECT 
    b.booking_id,
    b.start_date,
    b.end_date,
    b.total_price,
    b.booking_status,
    b.created_at,
    e.equipment_name,
    e.type,
    e.image
FROM bookings b
LEFT JOIN equipments e ON b.equipment_id = e.id
WHERE b.user_id = ?
ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];

while ($row = $result->fetch_assoc()) {
    // Add full URL for image
    if (!empty($row["image"])) {
        $row["image"] = "https://k1zsv0c4-80.inc1.devtunnels.ms/Rentingmanagement/uploads/" . $row["image"];
    }
    $bookings[] = $row;
}

if (count($bookings) > 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Bookings fetched successfully",
        "data" => $bookings
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "No bookings found",
        "data" => []
    ]);
}

$stmt->close();
$conn->close();
?>
