<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";  // include your db.php connection file

// Read input
$email    = isset($_POST['email']) ? trim($_POST['email']) : "";
$password = isset($_POST['password']) ? trim($_POST['password']) : "";

// Validation
if ($email == "" || $password == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email and Password are required"
    ]);
    exit;
}

// Email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email format"
    ]);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// If user not found
if ($result->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email not found"
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Check password ( normal text check )
if ($user['password'] !== $password) {
    echo json_encode([
        "status" => "error",
        "message" => "Incorrect password"
    ]);
    exit;
}

// Success response
echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "data" => [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "password" => $user['password'] // returning normal text password
    ]
]);

$stmt->close();
$conn->close();
?>