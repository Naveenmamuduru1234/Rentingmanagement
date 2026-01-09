<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

$operator_id = isset($_GET['operator_id']) ? intval($_GET['operator_id']) : 0;

if ($operator_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid operator ID"
    ]);
    exit;
}

// Fetch operator details with rating
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
WHERE o.operator_id = ?
GROUP BY o.operator_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $operator_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $operator = $result->fetch_assoc();
    
    $operator["avg_rating"] = round(floatval($operator["avg_rating"]), 1);
    $operator["jobs_completed"] = intval($operator["jobs_completed"]);
    $operator["price_per_hour"] = 300;
    $operator["distance_km"] = round(rand(10, 60) / 10, 1);
    $operator["is_verified"] = true;
    
    // Fetch recent reviews
    $reviewSql = "SELECT 
        r.rating,
        r.comment,
        r.created_at,
        u.name as reviewer_name
    FROM ratings r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.operator_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5";
    
    $reviewStmt = $conn->prepare($reviewSql);
    $reviewStmt->bind_param("i", $operator_id);
    $reviewStmt->execute();
    $reviewResult = $reviewStmt->get_result();
    
    $reviews = [];
    while ($review = $reviewResult->fetch_assoc()) {
        $reviews[] = $review;
    }
    $operator["reviews"] = $reviews;
    $reviewStmt->close();
    
    echo json_encode([
        "status" => "success",
        "message" => "Operator details fetched successfully",
        "data" => $operator
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Operator not found"
    ]);
}

$stmt->close();
$conn->close();
?>
