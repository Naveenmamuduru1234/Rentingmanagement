<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log

// Create equipments table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS equipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(255) NOT NULL,
    specification TEXT,
    type VARCHAR(100),
    price_per_hour DECIMAL(10,2),
    availability VARCHAR(50) DEFAULT 'Available',
    year VARCHAR(10),
    model VARCHAR(100),
    fuel_type VARCHAR(50),
    power VARCHAR(50),
    insurance VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    owner_id INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_owner_id (owner_id),
    INDEX idx_availability (availability)
)");

// Form Data
$equipment_name = $_POST["equipment_name"] ?? "";
$specification  = $_POST["specification"] ?? "";
$type           = $_POST["type"] ?? "";
$price_per_hour = $_POST["price_per_hour"] ?? "0";
$availability   = $_POST["availability"] ?? "Available";
$year           = $_POST["year"] ?? "";
$model          = $_POST["model"] ?? "";
$fuel_type      = $_POST["fuel_type"] ?? "";
$power          = $_POST["power"] ?? "";
$insurance      = $_POST["insurance"] ?? "";
$description    = $_POST["description"] ?? "";
$owner_id       = isset($_POST["owner_id"]) ? intval($_POST["owner_id"]) : 0;

// Log received data for debugging
error_log("Add Equipment Request - Name: $equipment_name, Type: $type, Price: $price_per_hour, Owner: $owner_id");

// Required Validation
if ($equipment_name == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Equipment name is required"
    ]);
    exit;
}

if ($price_per_hour == "" || $price_per_hour == "0") {
    echo json_encode([
        "status" => "error",
        "message" => "Price per hour is required"
    ]);
    exit;
}

// Convert price to float
$price_per_hour = floatval($price_per_hour);

// =====================
// IMAGE UPLOAD
// =====================
$image_name = "";

if (!empty($_FILES["image"]["name"])) {
    $target_dir = "uploads/";
    
    // Create folder if not exist
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to create uploads directory"
            ]);
            exit;
        }
    }

    // Check for upload errors
    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => "File too large (exceeds server limit)",
            UPLOAD_ERR_FORM_SIZE => "File too large (exceeds form limit)",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
            UPLOAD_ERR_NO_FILE => "No file was uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
            UPLOAD_ERR_EXTENSION => "File upload stopped by extension"
        ];
        $errorCode = $_FILES["image"]["error"];
        $errorMsg = $uploadErrors[$errorCode] ?? "Unknown upload error";
        
        echo json_encode([
            "status" => "error",
            "message" => "Image upload error: $errorMsg"
        ]);
        exit;
    }

    // File extension
    $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    
    // Validate extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid image format. Allowed: jpg, jpeg, png, gif, webp"
        ]);
        exit;
    }

    // Unique name
    $image_name = time() . "_" . rand(1000, 9999) . "." . $ext;

    // Full path
    $image_path = $target_dir . $image_name;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to save uploaded image"
        ]);
        exit;
    }
    
    error_log("Image uploaded successfully: $image_name");
} else {
    error_log("No image file received in request");
}

// =====================
// INSERT QUERY
// =====================
$stmt = $conn->prepare("
INSERT INTO equipments 
(equipment_name, specification, type, price_per_hour, availability, year, model, fuel_type, power, insurance, description, image, owner_id)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database prepare failed: " . $conn->error
    ]);
    exit;
}

// All string types except price (d for double) and owner_id (i for integer)
$stmt->bind_param(
    "sssdssssssssi",
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
    $image_name,
    $owner_id
);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    error_log("Equipment added successfully with ID: $newId");
    
    echo json_encode([
        "status" => "success",
        "message" => "Equipment added successfully",
        "equipment_id" => $newId,
        "image" => $image_name
    ]);
} else {
    error_log("Insert failed: " . $stmt->error);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to add equipment: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>

