<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$email = isset($_POST['email']) ? trim($_POST['email']) : "";
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : "";

// Validation
if ($email == "" || $otp == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email and OTP are required"
    ]);
    exit;
}

// Check OTP
$stmt = $conn->prepare("SELECT id, expires_at, used FROM password_reset_otp WHERE email=? AND otp=? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid OTP"
    ]);
    exit;
}

$row = $result->fetch_assoc();

// Check if OTP is already used
if ($row['used'] == 1) {
    echo json_encode([
        "status" => "error",
        "message" => "OTP already used"
    ]);
    exit;
}

// Check if OTP is expired
$expires_at = strtotime($row['expires_at']);
if (time() > $expires_at) {
    echo json_encode([
        "status" => "error",
        "message" => "OTP has expired"
    ]);
    exit;
}

// Mark OTP as used
$update = $conn->prepare("UPDATE password_reset_otp SET used=1 WHERE id=?");
$update->bind_param("i", $row['id']);
$update->execute();
$update->close();

echo json_encode([
    "status" => "success",
    "message" => "OTP verified successfully"
]);

$stmt->close();
$conn->close();
?>
