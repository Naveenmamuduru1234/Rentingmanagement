<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Get owner_id from query parameter
$owner_id = isset($_GET['owner_id']) ? intval($_GET['owner_id']) : 0;

// Base URL for images
$base_url = "https://k1zsv0c4-80.inc1.devtunnels.ms/Rentingmanagement/uploads/";

// Fetch equipment for the owner
if ($owner_id > 0) {
    $sql = "SELECT e.*, 
            (SELECT COUNT(*) FROM bookings WHERE equipment_id = e.id) as booking_count
            FROM equipments e 
            WHERE e.owner_id = ? 
            ORDER BY e.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If no owner_id, return all equipment (fallback)
    $sql = "SELECT e.*, 
            (SELECT COUNT(*) FROM bookings WHERE equipment_id = e.id) as booking_count
            FROM equipments e 
            ORDER BY e.id DESC";
    $result = $conn->query($sql);
}

$equipments = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add full URL for image
        $image_url = "";
        if (!empty($row["image"])) {
            $image_url = $base_url . $row["image"];
        }
        
        $equipments[] = [
            "id" => intval($row["id"]),
            "name" => $row["equipment_name"],
            "type" => $row["type"],
            "price_per_hour" => $row["price_per_hour"],
            "availability" => $row["availability"],
            "bookings" => strval($row["booking_count"] ?? 0),
            "image_url" => $image_url
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Equipment fetched successfully",
        "data" => $equipments
    ]);

} else {
    echo json_encode([
        "status" => "success",
        "message" => "No equipment found",
        "data" => []
    ]);
}

$conn->close();
?>
