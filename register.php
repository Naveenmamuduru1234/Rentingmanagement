<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php"; // DB connection

// Read input
$name     = isset($_POST['name']) ? trim($_POST['name']) : "";
$email    = isset($_POST['email']) ? trim($_POST['email']) : "";
$mobile   = isset($_POST['mobile']) ? trim($_POST['mobile']) : "";
$password = isset($_POST['password']) ? trim($_POST['password']) : "";

// Input validation
if ($name == "" || $email == "" || $mobile == "" || $password == "") {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email format"
    ]);
    exit;
}

// Mobile validation (10 digits)
if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid mobile number"
    ]);
    exit;
}

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email already registered"
    ]);
    exit;
}

// Insert user (password stored as plain text – not recommended for production)
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $name, $email, $mobile, $password);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "User registered successfully",
        "data" => [
            "id" => $stmt->insert_id,
            "name" => $name,
            "email" => $email,
            "mobile" => $mobile,
            "password" => $password
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Registration failed"
    ]);
}

$stmt->close();
$conn->close();
?>
