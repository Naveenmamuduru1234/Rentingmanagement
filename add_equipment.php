<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

// Form Data
$equipment_name = $_POST["equipment_name"] ?? "";
$specification  = $_POST["specification"] ?? "";
$type           = $_POST["type"] ?? "";
$price_per_hour = $_POST["price_per_hour"] ?? "";
$availability   = $_POST["availability"] ?? "";
$year           = $_POST["year"] ?? "";
$model          = $_POST["model"] ?? "";
$fuel_type      = $_POST["fuel_type"] ?? "";
$power          = $_POST["power"] ?? "";
$insurance      = $_POST["insurance"] ?? "";
$description    = $_POST["description"] ?? "";

// Required Validation
if ($equipment_name == "" || $price_per_hour == "") {
    echo json_encode([
        "status" => "error",
        "message" => "equipment_name and price_per_hour are required"
    ]);
    exit;
}

// =====================
// IMAGE UPLOAD
// =====================
$image_name = "";

if (!empty($_FILES["image"]["name"])) {
    $target_dir = "uploads/";
    
    // Create folder if not exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // File extension
    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

    // Unique name
    $image_name = time() . "_" . rand(1000, 9999) . "." . $ext;

    // Full path
    $image_path = $target_dir . $image_name;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
        echo json_encode([
            "status" => "error",
            "message" => "Image upload failed"
        ]);
        exit;
    }
}

// =====================
// INSERT QUERY
// =====================
$stmt = $conn->prepare("
INSERT INTO equipments 
(equipment_name, specification, type, price_per_hour, availability, year, model, fuel_type, power, insurance, description, image)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssdsissssss",
    $equipment_name,
    $specification,
    $type,
    $price_per_hour,
    $availability,
    $year,
    $model,
    $fuel_type,
    $power,
    $insurance,
    $description,
    $image_name
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Equipment added successfully",
        "equipment_id" => $stmt->insert_id,
        "image" => $image_name
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
