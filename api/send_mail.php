<?php
// Допоміжний клас для відправки стилізованої HTML-пошти з власними шрифтами
require_once __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../includes/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a styled HTML email using PHPMailer.
 *
 * @param string $to Recipient email
 * @param string $to_name Recipient name (optional)
 * @param string $subject Email subject
 * @param string $body_html Main HTML content (inside the template)
 * @param string $alt_body Optional plain text alternative
 * @param array $options Optional array for overrides (e.g., 'from', 'from_name')
 * @return bool|string True on success, error message on failure
 */
function send_custom_mail($to, $to_name, $subject, $body_html, $alt_body = '', $options = []) {
    $mail = new PHPMailer(true);
    try {
        // SMTP налаштування
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'weareinukrainesup77@gmail.com';
        $mail->Password = 'kwfz jdwt wsgr wied';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Відправник
        $from = $options['from'] ?? 'weareinukrainesup77@gmail.com';
        $from_name = $options['from_name'] ?? 'Ми в Україні';
        $mail->setFrom($from, $from_name);

        // Приймач
        $mail->addAddress($to, $to_name ?: '');

        // Тема
        $mail->Subject = $subject;
        $mail->isHTML(true);
    
        // HTML Email Template
        $html = "<html><head><meta charset='UTF-8'>
        <link href='https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i' rel='stylesheet'>
        <link href='https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i,900,900i' rel='stylesheet'>
        <style>
            body { background: #f6f6f6; margin: 0; padding: 0; }
            .container { max-width: 520px; margin: 32px auto; background: #fff; border-radius: 8px; box-shadow: 0 1px 8px #0001; padding: 32px 24px; }
            .header { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: #000000; margin-bottom: 16px; }
            .content { font-family: 'Montserrat', Arial, sans-serif; font-size: 16px; color: #000000; line-height: 1.6; }
            .footer { margin-top: 32px; font-family: 'Playfair Display', serif; font-size: 13px; color: #888; text-align: center; }
        </style></head><body><div class='container'>
            <div class='header'>Ми в Україні</div>
            <div class='content'>$body_html</div>
            <div class='footer'>&copy; " . date('Y') . " Ми в Україні. Всі права захищені.</div>
        </div></body></html>";
        $mail->Body = $html;
        $mail->AltBody = $alt_body ?: strip_tags($body_html);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return $mail->ErrorInfo;
    }
}
