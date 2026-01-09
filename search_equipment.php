<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Get search query from parameter
$query = isset($_GET['query']) ? trim($_GET['query']) : "";

if (empty($query)) {
    // If no query, return all equipment
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
        COALESCE(AVG(r.rating), 0) as avg_rating
    FROM equipments e
    LEFT JOIN ratings r ON e.id = r.equipment_id
    GROUP BY e.id
    ORDER BY e.id DESC
    LIMIT 50";
    
    $result = $conn->query($sql);
} else {
    // Search in equipment_name, type, description, model
    $searchParam = "%" . $query . "%";
    
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
        COALESCE(AVG(r.rating), 0) as avg_rating
    FROM equipments e
    LEFT JOIN ratings r ON e.id = r.equipment_id
    WHERE e.equipment_name LIKE ? 
        OR e.type LIKE ? 
        OR e.description LIKE ? 
        OR e.model LIKE ?
    GROUP BY e.id
    ORDER BY e.id DESC
    LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
}

$equipments = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add full URL for image
        if (!empty($row["image"])) {
            $row["image_url"] = "https://k1zsv0c4-80.inc1.devtunnels.ms/Rentingmanagement/uploads/" . $row["image"];
        } else {
            $row["image_url"] = "";
        }
        // Also map equipment_name to name for Android compatibility
        $row["name"] = $row["equipment_name"];
        $row["avg_rating"] = round(floatval($row["avg_rating"]), 1);
        $equipments[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "message" => "Equipment found",
        "count" => count($equipments),
        "data" => $equipments
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "No equipment found",
        "count" => 0,
        "data" => []
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
