<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$equipment_id = isset($_GET['equipment_id']) ? intval($_GET['equipment_id']) : 0;

if ($equipment_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "equipment_id is required"
    ]);
    exit;
}

$sql = "SELECT
            id,
            equipment_name,
            specification,
            type,
            price_per_hour,
            availability,
            year,
            model,
            fuel_type,
            power,
            insurance,
            description,
            image
        FROM equipments
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    // add full image URL
    $row['image'] = "https://k1zsv0c4-80.inc1.devtunnels.ms/Rentingmanagement/uploads/" . $row['image'];

    echo json_encode([
        "status" => "success",
        "data" => $row
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Equipment not found"
    ]);
}

$conn->close();
?>
