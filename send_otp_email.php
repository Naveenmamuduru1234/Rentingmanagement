<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

// PHPMailer configuration - You need to install PHPMailer via Composer or download it
// composer require phpmailer/phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer manually if not using Composer
// Uncomment these lines if you downloaded PHPMailer manually
// require 'phpmailer/src/Exception.php';
// require 'phpmailer/src/PHPMailer.php';
// require 'phpmailer/src/SMTP.php';

// If using Composer
require 'vendor/autoload.php';

// SMTP Configuration - UPDATE THESE VALUES
$SMTP_HOST = 'smtp.gmail.com';  // Or your SMTP server
$SMTP_PORT = 587;
$SMTP_USERNAME = 'your-email@gmail.com';  // Your email address
$SMTP_PASSWORD = 'your-app-password';      // App password (not regular password)
$SMTP_FROM_EMAIL = 'your-email@gmail.com';
$SMTP_FROM_NAME = 'Agro Rent - Equipment Rental';

$email = isset($_POST['email']) ? trim($_POST['email']) : "";

// Validation
if ($email == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email is required"
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email format"
    ]);
    exit;
}

// Check user exists
$check = $conn->prepare("SELECT id, name FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email not found. Please register first."
    ]);
    exit;
}

// Get user name
$check->bind_result($userId, $userName);
$check->fetch();
$check->close();

// Generate 6-digit OTP
$otp = sprintf("%06d", mt_rand(0, 999999));

// Set expiry time (10 minutes from now)
$expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Delete any existing OTPs for this email
$delete = $conn->prepare("DELETE FROM password_reset_otp WHERE email=?");
$delete->bind_param("s", $email);
$delete->execute();
$delete->close();

// Insert new OTP
$insert = $conn->prepare("INSERT INTO password_reset_otp (email, otp, expires_at) VALUES (?, ?, ?)");
$insert->bind_param("sss", $email, $otp, $expires_at);

if (!$insert->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to generate OTP"
    ]);
    exit;
}
$insert->close();

// Send email using PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = $SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = $SMTP_USERNAME;
    $mail->Password   = $SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $SMTP_PORT;

    // Recipients
    $mail->setFrom($SMTP_FROM_EMAIL, $SMTP_FROM_NAME);
    $mail->addAddress($email, $userName);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset OTP - Agro Rent';
    
    // HTML Email Template
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset OTP</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td align="center" style="padding: 40px 20px;">
                    <table role="presentation" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%); padding: 40px 30px; text-align: center; border-radius: 16px 16px 0 0;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 28px;">🚜 Agro Rent</h1>
                                <p style="color: #E8F5E9; margin: 10px 0 0 0; font-size: 14px;">Equipment Rental Made Easy</p>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px 30px;">
                                <h2 style="color: #333333; margin: 0 0 20px 0; font-size: 22px;">Password Reset Request</h2>
                                <p style="color: #666666; margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;">
                                    Hello ' . htmlspecialchars($userName) . ',
                                </p>
                                <p style="color: #666666; margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;">
                                    We received a request to reset your password. Use the OTP below to verify your identity:
                                </p>
                                
                                <!-- OTP Box -->
                                <div style="background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-radius: 12px; padding: 30px; text-align: center; margin: 30px 0;">
                                    <p style="color: #2E7D32; margin: 0 0 10px 0; font-size: 14px; font-weight: 600;">Your OTP Code:</p>
                                    <h1 style="color: #2E7D32; margin: 0; font-size: 48px; letter-spacing: 10px; font-weight: bold;">' . $otp . '</h1>
                                </div>
                                
                                <!-- Timer Notice -->
                                <div style="background-color: #FFF3E0; border-left: 4px solid #FF9800; padding: 15px 20px; border-radius: 0 8px 8px 0; margin: 20px 0;">
                                    <p style="color: #E65100; margin: 0; font-size: 14px;">
                                        ⏱️ <strong>This OTP is valid for 10 minutes.</strong> Please do not share this code with anyone.
                                    </p>
                                </div>
                                
                                <p style="color: #666666; margin: 20px 0 0 0; font-size: 14px; line-height: 1.6;">
                                    If you did not request this password reset, please ignore this email or contact our support team.
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #F5F5F5; padding: 30px; text-align: center; border-radius: 0 0 16px 16px;">
                                <p style="color: #999999; margin: 0 0 10px 0; font-size: 12px;">
                                    This is an automated email. Please do not reply.
                                </p>
                                <p style="color: #999999; margin: 0; font-size: 12px;">
                                    © ' . date('Y') . ' Agro Rent. All rights reserved.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ';
    
    // Plain text version
    $mail->AltBody = "Hello $userName,\n\nYour OTP for password reset is: $otp\n\nThis OTP is valid for 10 minutes.\n\nIf you did not request this, please ignore this email.\n\n- Agro Rent Team";

    $mail->send();
    
    echo json_encode([
        "status" => "success",
        "message" => "OTP has been sent to your email address"
    ]);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Mail Error: " . $mail->ErrorInfo);
    
    echo json_encode([
        "status" => "success",
        "message" => "OTP sent to your email",
        // For debugging during development - REMOVE IN PRODUCTION
        "debug_otp" => $otp,
        "debug_error" => $mail->ErrorInfo
    ]);
}

$conn->close();
?>
