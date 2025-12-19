<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "db.php";

$email = isset($_POST['email']) ? trim($_POST['email']) : "";
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : "";

// Validation
if ($email == "" || $new_password == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email and new_password are required"
    ]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email format"
    ]);
    exit;
}

// Hash password (IMPORTANT for security)
// $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Check user exists
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email not found"
    ]);
    exit;
}

// Update password
$update = $conn->prepare("UPDATE users SET password=? WHERE email=?");
$update->bind_param("ss", $new_password, $email);

if ($update->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Password updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update password"
    ]);
}

$update->close();
$check->close();
$conn->close();
?>
