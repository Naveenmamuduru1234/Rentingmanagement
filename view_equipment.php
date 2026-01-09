<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Base URL for images - Using PC's IP for physical device
// For Android Emulator: use http://10.0.2.2/Rentingmanagement/uploads/
// For physical device: use your PC's IP
$base_url = "http://10.150.97.225/Rentingmanagement/uploads/";

// Check if owner_id filter is provided
$owner_id = isset($_GET['owner_id']) ? intval($_GET['owner_id']) : null;

// Fetch equipments - filter by owner if owner_id is provided
if ($owner_id !== null && $owner_id > 0) {
    $sql = "SELECT * FROM equipments WHERE owner_id = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM equipments ORDER BY id DESC";
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
        
        // Map fields to match Android EquipmentData model
        $equipments[] = [
            "id" => strval($row["id"]),
            "name" => $row["equipment_name"],  // Map equipment_name to name
            "equipment_name" => $row["equipment_name"],
            "price_per_hour" => $row["price_per_hour"],
            "availability" => $row["availability"],
            "image_url" => $image_url,
            "specification" => $row["specification"],
            "type" => $row["type"],
            "year" => $row["year"],
            "model" => $row["model"],
            "fuel_type" => $row["fuel_type"],
            "power" => $row["power"],
            "insurance" => $row["insurance"],
            "description" => $row["description"]
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Data fetched successfully",
        "data" => $equipments
    ]);

} else {
    // Return success with empty data array instead of error
    echo json_encode([
        "status" => "success",
        "message" => "No equipments found",
        "data" => []
    ]);
}

$conn->close();
?>

