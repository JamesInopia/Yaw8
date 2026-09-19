<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public static function sendResetCode(string $toEmail, string $code): bool {
        $config = require __DIR__ . '/../config/mail.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['port'];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Your YA!W8 password reset code';
            $mail->Body    = "<p>Your password reset code is:</p>
                              <h2 style=\"letter-spacing:6px;\">{$code}</h2>
                              <p>This code expires in 10 minutes. If you didn't request this, ignore this email.</p>";
            $mail->AltBody = "Your YA!W8 password reset code is {$code}. It expires in 10 minutes.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
