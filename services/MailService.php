<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once "../config/cors.php";

class MailService
{
    public static function enviar(
        string $paraEmail,
        string $paraNome,
        string $assunto,
        string $html
    ) {
        $config = require __DIR__ . '/../config/mail.php';

        $mail = new PHPMailer(true);

        try {
            // SMTP
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['port'];

            // Charset
            $mail->CharSet = 'UTF-8';

            // From
            $mail->setFrom(
                $config['from_email'],
                $config['from_name']
            );

            // To
            $mail->addAddress($paraEmail, $paraNome);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $html;

            $mail->send();
            return true;

        } catch (Exception $e) {
            throw new Exception(
                "Falha ao enviar e-mail: " . $mail->ErrorInfo
            );
        }
    }
}
