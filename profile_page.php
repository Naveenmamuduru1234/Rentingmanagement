<?php
header("Content-Type: application/json");
require "db.php";

$id = $_POST["id"] ?? "";

if ($id == "") {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

// Dynamic update fields
$fields = [];

// Update name
if (isset($_POST["name"]) && $_POST["name"] !== "") {
    $fields[] = "name='" . $conn->real_escape_string($_POST["name"]) . "'";
}

// Update email
if (isset($_POST["email"]) && $_POST["email"] !== "") {
    $fields[] = "email='" . $conn->real_escape_string($_POST["email"]) . "'";
}

// Update password (optional)
if (isset($_POST["password"]) && $_POST["password"] !== "") {
    $fields[] = "password='" . $conn->real_escape_string($_POST["password"]) . "'";
}

// If no fields to update
if (count($fields) == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No fields to update"
    ]);
    exit;
}

// UPDATE QUERY
$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => "Profile updated successfully"
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
