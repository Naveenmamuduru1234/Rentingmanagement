<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php"; // DB connection

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validation
if (!isset($input['equipment_id']) || !isset($input['insurance_company']) || 
    !isset($input['policy_number']) || !isset($input['start_date']) || 
    !isset($input['end_date']) || !isset($input['insurance_amount']) || 
    !isset($input['insurance_type'])) {

    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

// Assign values
$equipment_id       = $input['equipment_id'];
$insurance_company  = $input['insurance_company'];
$policy_number      = $input['policy_number'];
$insurance_type     = $input['insurance_type'];   // <-- Added this
$start_date         = $input['start_date'];
$end_date           = $input['end_date'];
$insurance_amount   = $input['insurance_amount'];
$damage_deductible  = $input['damage_deductible'] ?? "0";
$remarks            = $input['remarks'] ?? "";

// 🔍 CHECK IF EQUIPMENT EXISTS
$checksql = "SELECT * FROM equipments WHERE id='$equipment_id' LIMIT 1";
$check = $conn->query($checksql);

if ($check->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid equipment ID! Equipment does not exist."
    ]);
    exit;
}

// INSERT DATA
$sql = "INSERT INTO equipment_insurance 
       (equipment_id, insurance_company, policy_number, insurance_type, start_date, end_date, insurance_amount, damage_deductible, remarks)
      VALUES ('$equipment_id', '$insurance_company', '$policy_number', '$insurance_type', '$start_date', '$end_date', '$insurance_amount', '$damage_deductible', '$remarks')";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => "Insurance details added successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database insert failed",
        "error"   => $conn->error
    ]);
}
?>
