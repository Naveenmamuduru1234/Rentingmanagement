<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php";

$equipment_id = $_GET['equipment_id'] ?? "";
$insurance_id = $_GET['insurance_id'] ?? "";

if($insurance_id != ""){
    $sql = "SELECT * FROM equipment_insurance WHERE id='$insurance_id'";
}
else if($equipment_id != ""){
    $sql = "SELECT * FROM equipment_insurance WHERE equipment_id='$equipment_id'";
}
else{
    echo json_encode(["status"=>"error","message"=>"Send equipment_id or insurance_id"]);
    exit;
}

$result = $conn->query($sql);
$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode(["status"=>"success","data"=>$data]);
?>
