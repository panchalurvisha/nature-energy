<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Script to process the email queue
 * Can be run via CLI/Cron: php process_mail_queue.php
 * Or via web for manual trigger
 */

$conn = getDbConnection();

// Fetch pending or failed (with < 3 attempts) emails
$sql = "SELECT q.*, c.name, c.company, c.phone, c.email as contact_email, c.product, c.message 
        FROM mail_queue q 
        JOIN contacts c ON q.contact_id = c.id 
        WHERE q.status IN ('pending', 'failed') AND q.attempts < 3
        LIMIT 10"; // Process 10 at a time to keep it fast

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    if (php_sapi_name() === 'cli') {
        echo "Queue empty.\n";
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Queue empty.",
            'stats' => ['processed' => 0, 'sent' => 0, 'failed' => 0]
        ]);
        exit;
    }
}

$processed = 0;
$sent = 0;
$failed = 0;

while ($row = $result->fetch_assoc()) {
    $processed++;
    $mailId = $row['id'];
    $type = $row['email_type'];
    $recipient = $row['recipient'];
    
    // Increment attempts
    $conn->query("UPDATE mail_queue SET attempts = attempts + 1 WHERE id = $mailId");

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($recipient);

        if ($type === 'admin') {
            // Admin Notification Email
            $mail->Subject = "🔔 New Inquiry #{$row['contact_id']} — {$row['name']}";
            $mail->isHTML(true);
            $mail->Body = "
            <html><body style='font-family:Arial,sans-serif;margin:0;padding:20px;background:#f0f5f7;'>
                <div style='max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);'>
                    <div style='background:linear-gradient(135deg,#078DA4,#066F82);padding:24px 28px;'>
                        <h2 style='color:#fff;margin:0;font-size:20px;'>New Contact Inquiry</h2>
                    </div>
                    <div style='padding:28px;'>
                        <p><strong>Name:</strong> {$row['name']}</p>
                        <p><strong>Company:</strong> {$row['company']}</p>
                        <p><strong>Phone:</strong> {$row['phone']}</p>
                        <p><strong>Email:</strong> {$row['contact_email']}</p>
                        <p><strong>Product:</strong> {$row['product']}</p>
                        <p><strong>Message:</strong><br>{$row['message']}</p>
                    </div>
                    <div style='padding:16px;background:#f8fafb;font-size:12px;color:#9AAAB5;text-align:center;'>
                        Queued Processing • Ref: #{$row['contact_id']}
                    </div>
                </div>
            </body></html>";
        } else {
            // Customer Auto-Reply
            $mail->Subject = "We received your inquiry — Nature Organic Energy";
            $mail->isHTML(true);
            $mail->Body = "
            <html><body style='font-family:Arial,sans-serif;margin:0;padding:20px;background:#f0f5f7;'>
                <div style='max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);'>
                    <div style='background:linear-gradient(135deg,#078DA4,#066F82);padding:28px;text-align:center;'>
                        <h2 style='color:#fff;margin:0;font-size:22px;'>Thank You, {$row['name']}!</h2>
                        <p style='color:rgba(255,255,255,0.8);margin:0;'>We've received your inquiry and will get back to you shortly.</p>
                    </div>
                    <div style='padding:28px;'>
                        <p>We have received your message regarding <strong>{$row['product']}</strong>. Our team will review it and contact you within 24 hours.</p>
                    </div>
                    <div style='padding:16px;background:#f8fafb;text-align:center;font-size:12px;color:#9AAAB5;'>
                        Nature Organic Energy • Ahmedabad • Ref: #{$row['contact_id']}
                    </div>
                </div>
            </body></html>";
        }

        $mail->send();
        
        // Mark as sent
        $conn->query("UPDATE mail_queue SET status = 'sent', sent_at = CURRENT_TIMESTAMP WHERE id = $mailId");
        $sent++;

    } catch (Exception $e) {
        $failed++;
        $errorMsg = $conn->real_escape_string($mail->ErrorInfo);
        $conn->query("UPDATE mail_queue SET status = 'failed', last_error = '$errorMsg' WHERE id = $mailId");
    }
}

$conn->close();

if (php_sapi_name() === 'cli') {
    echo "Processed: $processed | Sent: $sent | Failed: $failed\n";
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "Email processing complete.",
        'stats' => ['processed' => $processed, 'sent' => $sent, 'failed' => $failed]
    ]);
}
?>
