<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// DB connection
include('db.php');
// get ?type=tractor (default: tractor)
$type = isset($_GET["type"]) ? $_GET["type"] : "tractor";

// prepare query
$sql = "SELECT 
            id,
            name,
            type,
            hp,
            fuel_type,
            distance,
            price,
            rating,
            availability,
            icon_url
        FROM equipment
        WHERE type = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $type);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);

mysqli_stmt_close($stmt);
mysqli_close($conn);
