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

// First get image name to delete
$img = $conn->query("SELECT image FROM equipments WHERE id='$id'")->fetch_assoc();
if ($img && file_exists("uploads/" . $img["image"])) {
    unlink("uploads/" . $img["image"]); // Delete file
}

// Delete row from DB
if ($conn->query("DELETE FROM equipments WHERE id='$id'")) {
    echo json_encode([
        "status" => "success",
        "message" => "Equipment deleted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Delete failed"
    ]);
}

$conn->close();
?>
