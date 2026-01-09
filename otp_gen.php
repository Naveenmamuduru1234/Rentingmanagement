<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Origin");

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = file_get_contents('php://input');
$json_data = json_decode($data, true);

if (isset($json_data['email'])) {
    $userEmail    = $json_data['email'];

    // $userEmail = "mamudurun@gmail.com";
    $userName = "mamu";

    $otp = 456789;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // change to localhost if using local mail server
        $mail->SMTPAuth = true;
        $mail->Username = 'mamudurun@gmail.com';
        $mail->Password = 'czcy nvox evhx acou';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mamudurun@gmail.com', 'Agro Rent');
        $mail->addAddress($userEmail, $userName);

        // Embed image (college logo/banner)
        $mail->AddEmbeddedImage('image/logo.webp', 'app_logo');

        $mail->isHTML(true);
        $mail->Subject = 'Thank You for Choosing Saveetha Medical College';

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; padding:20px; background:#f9f9f9;'>
            <div style='text-align:center;'>
                <img src='cid:app_logo' alt='App Logo' style='width:150px; margin-bottom:15px;'>
                <h2 style='color:#0056b3;'>Thank You for Choosing Agro Rent</h2>
            </div>
            
            <p>Dear <b>$userName</b>,</p>
            <p>We have registered a new otp sent to your mail</p>
            <p style='background:#e9f5ff; padding:10px; border-radius:5px;'>
                <b>OTP:</b> $otp <br>

            </p>
            
            <p>Please keep your otp safe logins.</p>
            
           
            
            <div style='margin-top:30px; text-align:center;'>
                <p style='color:#777;'>Agro Rent<br>
                Chennai, Tamil Nadu</p>
            </div>
        </div>
        ";

        $mail->AltBody = "Dear $userName, Thank you for choosing Agro Rent.";

        $mail->send();

        echo json_encode(['status' => 200, 'message' => 'Thank you email sent to user']);
    } catch (Exception $e) {
        echo json_encode(['status' => 500, 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(['status' => 400, 'message' => 'Invalid data']);
}
?>