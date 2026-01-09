<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
include "db.php";

// SQL query (matches your table exactly)
$sql = "SELECT 
            id, 
            category_name, 
            description, 
            icon, 
            available_count 
        FROM equipment_categories 
        WHERE status = 1
        ORDER BY id DESC";

$result = $conn->query($sql);

// Query error check
if ($result === false) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL query failed",
        "error" => $conn->error
    ]);
    exit;
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row["id"],
        "category_name" => $row["category_name"],
        "description" => $row["description"],
        "icon" => $row["icon"],
        "available_count" => $row["available_count"],
        "icon_url" => "http://localhost/equipment/icons/" . $row["icon"]
    ];
}

echo json_encode([
    "status" => "success",
    "message" => count($data) > 0 ? "Equipment categories fetched" : "No categories found",
    "data" => $data
]);

$conn->close();
?>
