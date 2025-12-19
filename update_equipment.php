<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

$id = $_POST["id"] ?? "";

if ($id == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Equipment ID is required"
    ]);
    exit;
}

$fields = [];

// Check each value and append only if exists
if (isset($_POST["equipment_name"]) && $_POST["equipment_name"] !== "") {
    $fields[] = "equipment_name='" . $_POST["equipment_name"] . "'";
}

if (isset($_POST["specification"]) && $_POST["specification"] !== "") {
    $fields[] = "specification='" . $_POST["specification"] . "'";
}

if (isset($_POST["type"]) && $_POST["type"] !== "") {
    $fields[] = "type='" . $_POST["type"] . "'";
}

if (isset($_POST["price_per_hour"]) && $_POST["price_per_hour"] !== "") {
    $fields[] = "price_per_hour='" . $_POST["price_per_hour"] . "'";
}

if (isset($_POST["availability"]) && $_POST["availability"] !== "") {
    $fields[] = "availability='" . $_POST["availability"] . "'";
}

if (isset($_POST["year"]) && $_POST["year"] !== "") {
    $fields[] = "year='" . $_POST["year"] . "'";
}

if (isset($_POST["model"]) && $_POST["model"] !== "") {
    $fields[] = "model='" . $_POST["model"] . "'";
}

if (isset($_POST["fuel_type"]) && $_POST["fuel_type"] !== "") {
    $fields[] = "fuel_type='" . $_POST["fuel_type"] . "'";
}

if (isset($_POST["power"]) && $_POST["power"] !== "") {
    $fields[] = "power='" . $_POST["power"] . "'";
}

if (isset($_POST["insurance"]) && $_POST["insurance"] !== "") {
    $fields[] = "insurance='" . $_POST["insurance"] . "'";
}

if (isset($_POST["description"]) && $_POST["description"] !== "") {
    $fields[] = "description='" . $_POST["description"] . "'";
}

// ======================
// IMAGE UPDATE (optional)
// ======================
if (!empty($_FILES["image"]["name"])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $image_name = time() . "_" . rand(1000, 9999) . "." . $ext;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);

    $fields[] = "image='$image_name'";
}

// If no fields to update
if (count($fields) == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No fields to update"
    ]);
    exit;
}

$sql = "UPDATE equipments SET " . implode(", ", $fields) . " WHERE id='$id'";

if ($conn->query($sql)) {
    echo json_encode([
        "status" => "success",
        "message" => "Equipment updated successfully"
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
