<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

$id = $_POST["operator_id"] ?? "";

if ($id == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Operator ID is required"
    ]);
    exit;
}

// If operator has image (optional future), delete code goes here

// Delete operator row
$sql = "DELETE FROM operators WHERE operator_id='$id'";
if ($conn->query($sql)) {
    echo json_encode([
        "status" => "success",
        "message" => "Operator deleted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Delete failed"
    ]);
}

$conn->close();
?>
