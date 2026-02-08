<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function sendOtpEmail(string $toEmail, string $toName, string $otp): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // ✅ Your Gmail + APP PASSWORD (not normal password)
        $mail->Username   = 'ahamedhafeel29@gmail.com';
        $mail->Password   = 'rvlpeqlmmuztjoyl';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('ahamedhafeel29@gmail.com', 'Family Bridge');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code - Family Bridge';
        $mail->Body    = "
            <div style='font-family:Segoe UI,Arial,sans-serif'>
              <h2 style='margin:0 0 10px'>OTP Verification</h2>
              <p>Your OTP code is:</p>
              <div style='font-size:28px; font-weight:700; letter-spacing:6px; padding:12px 16px; background:#f4f6f8; display:inline-block; border-radius:10px'>
                {$otp}
              </div>
              <p style='margin-top:12px;color:#666'>This OTP will expire in 5 minutes.</p>
            </div>
        ";
        $mail->AltBody = "Your OTP is: {$otp}. It expires in 5 minutes.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return false;
    }
}
