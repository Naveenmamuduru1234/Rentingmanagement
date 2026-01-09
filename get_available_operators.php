<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Fetch all operators with ratings (removed strict 'Available' filter since DB has inconsistent values)
$sql = "SELECT 
    o.operator_id,
    o.operator_name,
    o.experience,
    o.specification,
    o.availability,
    COALESCE(AVG(r.rating), 4.5) as avg_rating,
    COUNT(r.id) as jobs_completed
FROM operators o
LEFT JOIN ratings r ON o.operator_id = r.operator_id
GROUP BY o.operator_id
ORDER BY avg_rating DESC";

$result = $conn->query($sql);

$operators = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add calculated fields
        $row["avg_rating"] = round(floatval($row["avg_rating"]), 1);
        $row["jobs_completed"] = intval($row["jobs_completed"]);
        $row["price_per_hour"] = 300; // Default operator rate
        $row["distance_km"] = round(rand(10, 60) / 10, 1); // Simulated distance
        $row["is_verified"] = true;
        
        $operators[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "message" => "Operators fetched successfully",
        "count" => count($operators),
        "data" => $operators
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "No operators found",
        "count" => 0,
        "data" => []
    ]);
}

$conn->close();
?>
