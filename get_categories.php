<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Get category counts from equipments table by type
$sql = "SELECT 
    type as category_name,
    COUNT(*) as available_count
FROM equipments 
WHERE availability LIKE '%Available%' OR availability LIKE '%available%'
GROUP BY type
ORDER BY available_count DESC";

$result = $conn->query($sql);

$categories = [];

// Define icons and descriptions for common categories
$categoryInfo = [
    "Tractor" => ["icon" => "ic_tractor", "description" => "Various HP tractors"],
    "Tractors" => ["icon" => "ic_tractor", "description" => "Various HP tractors"],
    "tractor" => ["icon" => "ic_tractor", "description" => "Various HP tractors"],
    "Harvester" => ["icon" => "ic_harvester", "description" => "Combine harvesters"],
    "Harvesters" => ["icon" => "ic_harvester", "description" => "Combine harvesters"],
    "Tiller" => ["icon" => "ic_tiller", "description" => "Rotary tillers"],
    "Tillers" => ["icon" => "ic_tiller", "description" => "Rotary tillers"],
    "Sprayer" => ["icon" => "ic_sprayer", "description" => "Pesticide sprayers"],
    "Sprayers" => ["icon" => "ic_sprayer", "description" => "Pesticide sprayers"],
    "Seeder" => ["icon" => "ic_seeder", "description" => "Seed drills"],
    "Seeders" => ["icon" => "ic_seeder", "description" => "Seed drills"],
    "Plough" => ["icon" => "ic_plough", "description" => "Disc & moldboard"],
    "Ploughs" => ["icon" => "ic_plough", "description" => "Disc & moldboard"],
    "Pump" => ["icon" => "ic_pump", "description" => "Water pumps"],
    "Pumps" => ["icon" => "ic_pump", "description" => "Water pumps"],
    "Thresher" => ["icon" => "ic_thresher", "description" => "Grain threshers"],
    "Threshers" => ["icon" => "ic_thresher", "description" => "Grain threshers"]
];

if ($result->num_rows > 0) {
    $id = 1;
    while ($row = $result->fetch_assoc()) {
        $categoryName = $row["category_name"];
        $info = isset($categoryInfo[$categoryName]) ? $categoryInfo[$categoryName] : ["icon" => "ic_settings", "description" => $categoryName . " equipment"];
        
        $categories[] = [
            "id" => strval($id),
            "category_name" => $categoryName,
            "description" => $info["description"],
            "icon" => $info["icon"],
            "available_count" => strval($row["available_count"]),
            "icon_url" => ""
        ];
        $id++;
    }
    
    echo json_encode([
        "status" => "success",
        "message" => "Categories fetched successfully",
        "data" => $categories
    ]);
} else {
    // Return default categories if no equipment found
    echo json_encode([
        "status" => "success",
        "message" => "No categories found",
        "data" => []
    ]);
}

$conn->close();
?>

