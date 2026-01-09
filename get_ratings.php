<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$equipment_id = isset($_GET['equipment_id']) ? intval($_GET['equipment_id']) : 0;

if ($equipment_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Equipment ID is required"
    ]);
    exit;
}

// Get ratings for equipment
$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.user_id,
        u.name as user_name,
        r.equipment_id,
        r.operator_id,
        r.rating,
        r.comment,
        r.created_at
    FROM ratings r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.equipment_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();

$ratings = [];
$totalRating = 0;
$count = 0;

while ($row = $result->fetch_assoc()) {
    $ratings[] = [
        "id" => intval($row['id']),
        "user_id" => intval($row['user_id']),
        "user_name" => $row['user_name'] ?? "Anonymous",
        "equipment_id" => intval($row['equipment_id']),
        "operator_id" => intval($row['operator_id']),
        "rating" => intval($row['rating']),
        "comment" => $row['comment'],
        "created_at" => $row['created_at']
    ];
    $totalRating += intval($row['rating']);
    $count++;
}

$averageRating = $count > 0 ? round($totalRating / $count, 1) : 0;

echo json_encode([
    "status" => "success",
    "data" => $ratings,
    "average_rating" => $averageRating
]);

$stmt->close();
$conn->close();
?>
