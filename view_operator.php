<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

// Fetch all operators
$sql = "SELECT * FROM operators ORDER BY operator_id DESC";
$result = $conn->query($sql);

$operators = [];

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        // Add Full image url (if you add image later)
        // $row["image_url"] = "http://localhost/rentingmanagement/uploads/" . $row["image"];

        $operators[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "message" => "Data fetched successfully",
        "data" => $operators
    ]);

} else {
    echo json_encode([
        "status" => "error",
        "message" => "No operators found"
    ]);
}

$conn->close();
?>
