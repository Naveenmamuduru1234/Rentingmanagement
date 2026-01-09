<?php
/**
 * Send OTP via Email - WORKING VERSION
 * 
 * SETUP INSTRUCTIONS:
 * 1. You need a Gmail account
 * 2. Enable 2-Factor Authentication on your Google Account
 * 3. Generate an App Password: 
 *    - Go to https://myaccount.google.com/apppasswords
 *    - Select "Mail" and "Other (Custom name)"
 *    - Name it "Agro Rent" and click Generate
 *    - Copy the 16-character password
 * 4. Update the configuration below with your details
 * 5. Install PHPMailer: Run "composer require phpmailer/phpmailer" in this folder
 *    OR download from https://github.com/PHPMailer/PHPMailer
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

// ================================================================
// EMAIL CONFIGURATION - UPDATE THESE WITH YOUR GMAIL DETAILS
// ================================================================
$EMAIL_CONFIG = [
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_port'     => 587,
    'smtp_username' => 'your-email@gmail.com',      // <-- PUT YOUR GMAIL HERE
    'smtp_password' => 'xxxx xxxx xxxx xxxx',       // <-- PUT YOUR APP PASSWORD HERE (16 chars with spaces)
    'from_email'    => 'your-email@gmail.com',      // <-- SAME AS ABOVE
    'from_name'     => 'Agro Rent'
];
// ================================================================

// Check if PHPMailer is available
$phpmailerAvailable = false;
$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require $autoloadPath;
    $phpmailerAvailable = true;
} else {
    // Try manual PHPMailer path
    $manualPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
    if (file_exists($manualPath)) {
        require __DIR__ . '/PHPMailer/src/Exception.php';
        require __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require __DIR__ . '/PHPMailer/src/SMTP.php';
        $phpmailerAvailable = true;
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Get email from request
$email = isset($_POST['email']) ? trim($_POST['email']) : "";

// Validation
if ($email == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email is required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email format"
    ]);
    exit;
}

// Create OTP table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS password_reset_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
)");

// Check if user exists
$check = $conn->prepare("SELECT id, name FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email not registered. Please sign up first."
    ]);
    $check->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$userName = $user['name'];
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
        "message" => "Failed to generate OTP. Please try again."
    ]);
    $insert->close();
    $conn->close();
    exit;
}
$insert->close();

// Send email
$emailSent = false;
$emailError = "";
$isConfigured = ($EMAIL_CONFIG['smtp_username'] !== 'your-email@gmail.com');

if ($phpmailerAvailable && $isConfigured) {
    try {
        $mail = new PHPMailer(true);
        
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = $EMAIL_CONFIG['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $EMAIL_CONFIG['smtp_username'];
        $mail->Password   = $EMAIL_CONFIG['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $EMAIL_CONFIG['smtp_port'];
        
        // For local testing - disable SSL verification
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom($EMAIL_CONFIG['from_email'], $EMAIL_CONFIG['from_name']);
        $mail->addAddress($email, $userName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Password Reset - Agro Rent';
        
        // Beautiful HTML email template
        $mail->Body = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 500px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%); padding: 30px; text-align: center; border-radius: 16px 16px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">🚜 Agro Rent</h1>
                            <p style="color: #E8F5E9; margin: 8px 0 0 0; font-size: 14px;">Password Reset Request</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #333; margin: 0 0 15px 0; font-size: 16px;">Hello <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                            <p style="color: #666; margin: 0 0 25px 0; font-size: 15px; line-height: 1.5;">
                                You requested to reset your password. Use the OTP below to verify your identity:
                            </p>
                            
                            <!-- OTP Box -->
                            <div style="background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-radius: 12px; padding: 25px; text-align: center; margin: 20px 0;">
                                <p style="color: #2E7D32; margin: 0 0 10px 0; font-size: 14px; font-weight: 600;">Your OTP Code</p>
                                <h1 style="color: #2E7D32; margin: 0; font-size: 42px; letter-spacing: 8px; font-weight: bold;">' . $otp . '</h1>
                            </div>
                            
                            <!-- Warning -->
                            <div style="background-color: #FFF3E0; border-left: 4px solid #FF9800; padding: 12px 15px; border-radius: 0 8px 8px 0; margin: 20px 0;">
                                <p style="color: #E65100; margin: 0; font-size: 13px;">
                                    ⏱️ <strong>Valid for 10 minutes only.</strong> Do not share this code with anyone.
                                </p>
                            </div>
                            
                            <p style="color: #999; margin: 20px 0 0 0; font-size: 13px;">
                                If you did not request this, please ignore this email.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f5f5f5; padding: 20px; text-align: center; border-radius: 0 0 16px 16px;">
                            <p style="color: #999; margin: 0; font-size: 11px;">
                                © ' . date('Y') . ' Agro Rent. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        
        // Plain text version
        $mail->AltBody = "Hello $userName,\n\nYour OTP for password reset is: $otp\n\nThis code is valid for 10 minutes.\n\nIf you did not request this, please ignore this email.\n\n- Agro Rent Team";

        $mail->send();
        $emailSent = true;
        
    } catch (Exception $e) {
        $emailError = $mail->ErrorInfo;
        error_log("OTP Email Error: " . $emailError);
    }
}

// Prepare response
$response = [
    "status" => "success"
];

if ($emailSent) {
    $response["message"] = "OTP has been sent to your email!";
    $response["email_sent"] = true;
} else {
    // Email not sent - provide debug info
    $response["message"] = "OTP generated successfully!";
    $response["debug_otp"] = $otp; // For testing - remove in production
    
    if (!$phpmailerAvailable) {
        $response["setup_required"] = "PHPMailer not installed. Run: composer require phpmailer/phpmailer";
    } elseif (!$isConfigured) {
        $response["setup_required"] = "Email not configured. Update EMAIL_CONFIG in send_otp.php";
    } else {
        $response["email_error"] = $emailError;
    }
}

echo json_encode($response);

$conn->close();
?>
