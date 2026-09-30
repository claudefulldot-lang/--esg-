<?php
namespace App\Helpers;

use App\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    /**
     * Send email using PHPMailer with settings from sys_settings
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT `setting_key`, `setting_value` FROM `sys_settings` WHERE `setting_key` LIKE 'smtp_%'");
            $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';

            // Server settings
            $host = $settings['smtp_host'] ?? 'localhost';
            $port = (int)($settings['smtp_port'] ?? 25);
            $user = $settings['smtp_user'] ?? '';
            $pass = $settings['smtp_pass'] ?? '';
            $fromName = $settings['smtp_from_name'] ?? 'ESG-Pro 智慧系統';

            if (!empty($user) && !empty($pass)) {
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $user;
                $mail->Password   = $pass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $port;
            }

            $mail->setFrom($user ?: 'no-reply@esg-pro.local', $fromName);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }
}
