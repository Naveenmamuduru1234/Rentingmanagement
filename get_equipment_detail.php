<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Get equipment_id from query parameter
$equipment_id = isset($_GET['equipment_id']) ? intval($_GET['equipment_id']) : 0;

if ($equipment_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid equipment ID"
    ]);
    exit;
}

// Fetch equipment details with average rating and total reviews
$sql = "SELECT 
    e.id,
    e.equipment_name,
    e.specification,
    e.type,
    e.price_per_hour,
    e.availability,
    e.year,
    e.model,
    e.fuel_type,
    e.power,
    e.insurance,
    e.description,
    e.image,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(r.id) as total_reviews
FROM equipments e
LEFT JOIN ratings r ON e.id = r.equipment_id
WHERE e.id = ?
GROUP BY e.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Add full URL for image
    if (!empty($row["image"])) {
        $row["image"] = "https://k1zsv0c4-80.inc1.devtunnels.ms/Rentingmanagement/uploads/" . $row["image"];
    }
    
    // Round the average rating to 1 decimal place
    $row["avg_rating"] = round(floatval($row["avg_rating"]), 1);
    $row["total_reviews"] = intval($row["total_reviews"]);
    
    // Add owner information (using default values since we don't have owner_id in equipments table)
    // You can modify this to fetch from users table if owner_id is added
    $row["owner_name"] = "Equipment Owner";
    $row["owner_rating"] = 4.5;
    
    echo json_encode([
        "status" => "success",
        "message" => "Equipment details fetched successfully",
        "data" => $row
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Equipment not found"
    ]);
}

$stmt->close();
$conn->close();
?>
