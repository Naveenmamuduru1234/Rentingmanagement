<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

// ====================
// FORM DATA
// ====================
$operator_name   = $_POST["operator_name"] ?? "";
$experience      = $_POST["experience"] ?? "";
$specification   = $_POST["specification"] ?? "";
$availability    = $_POST["availability"] ?? "";

// Required Validation
if ($operator_name == "") {
    echo json_encode([
        "status" => "error",
        "message" => "operator_name is required"
    ]);
    exit;
}

// =====================
// INSERT QUERY
// =====================
$stmt = $conn->prepare("
INSERT INTO operators (operator_name, experience, specification, availability)
VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "ssss",
    $operator_name,
    $experience,
    $specification,
    $availability
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Operator added successfully",
        "operator_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert failed",
        "sql_error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
