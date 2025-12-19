<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

$input = json_decode(file_get_contents("php://input"), true);

$insurance_id = $input["insurance_id"] ?? "";

if($insurance_id == ""){
    echo json_encode(["status"=>"error","message"=>"insurance_id required"]);
    exit;
}

$sql = "DELETE FROM equipment_insurance WHERE id='$insurance_id'";

if ($conn->query($sql)) {
    echo json_encode(["status"=>"success","message"=>"Insurance deleted"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Delete failed"]);
}
?>
