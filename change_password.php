<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'db.php';

// Get POST data
$email = $_POST['email'] ?? '';
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($email) || empty($current_password) || empty($new_password)) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

// Verify current password
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Verify current password (assuming passwords are hashed)
if (!password_verify($current_password, $user['password'])) {
    // Also check plain text for backward compatibility
    if ($current_password !== $user['password']) {
        echo json_encode([
            "status" => "error",
            "message" => "Current password is incorrect"
        ]);
        exit;
    }
}

// Update password (hash the new password)
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$update_stmt->bind_param("ss", $hashed_password, $email);

if ($update_stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Password changed successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to change password"
    ]);
}

$stmt->close();
$update_stmt->close();
$conn->close();
?>
