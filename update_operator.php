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

$fields = [];

// ======================
// UPDATE FIELDS IF PROVIDED
// ======================

if (isset($_POST["operator_name"]) && $_POST["operator_name"] !== "") {
    $fields[] = "operator_name='" . $_POST["operator_name"] . "'";
}

if (isset($_POST["experience"]) && $_POST["experience"] !== "") {
    $fields[] = "experience='" . $_POST["experience"] . "'";
}

if (isset($_POST["specification"]) && $_POST["specification"] !== "") {
    $fields[] = "specification='" . $_POST["specification"] . "'";
}

if (isset($_POST["availability"]) && $_POST["availability"] !== "") {
    $fields[] = "availability='" . $_POST["availability"] . "'";
}


// If no fields to update
if (count($fields) == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No fields to update"
    ]);
    exit;
}

// ======================
// UPDATE QUERY
// ======================
$sql = "UPDATE operators SET " . implode(", ", $fields) . " WHERE operator_id='$id'";

if ($conn->query($sql)) {
    echo json_encode([
        "status" => "success",
        "message" => "Operator updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Update failed",
        "sql_error" => $conn->error
    ]);
}

$conn->close();
?>
