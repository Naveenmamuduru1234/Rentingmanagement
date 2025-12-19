<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Fetch all equipments
$sql = "SELECT * FROM equipments ORDER BY id DESC";
$result = $conn->query($sql);

$equipments = [];

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        // Add full URL for image
        $row["image_url"] = "http://localhost/rentingmanagement/uploads/" . $row["image"];
        
        $equipments[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "message" => "Data fetched successfully",
        "data" => $equipments
    ]);

} else {
    echo json_encode([
        "status" => "error",
        "message" => "No equipments found"
    ]);
}

$conn->close();
?>
