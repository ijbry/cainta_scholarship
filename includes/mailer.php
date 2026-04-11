<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/PHPMailer-7.0.2/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/PHPMailer-7.0.2/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/PHPMailer-7.0.2/src/Exception.php';

function sendStatusEmail($to_email, $to_name, $status, $remarks = '') {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'caintascholars0@gmail.com';
        $mail->Password   = 'tutmntopzokbmoxa';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('caintascholars0@gmail.com', 'Cainta Scholarship Program');
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true);

        if($status == 'approved') {
            $mail->Subject = 'Scholarship Application Approved!';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: #1A3A6B; padding: 20px; text-align: center;'>
                    <h2 style='color: white; margin: 0;'>Cainta Scholarship Program</h2>
                    <p style='color: #B5D4F4; margin: 5px 0 0;'>Municipality of Cainta, Rizal</p>
                </div>
                <div style='padding: 30px; background: #f9f9f9;'>
                    <h3 style='color: #1A3A6B;'>Congratulations, {$to_name}! 🎉</h3>
                    <p>We are pleased to inform you that your scholarship application has been <strong style='color: #198754;'>APPROVED</strong>.</p>
                    <div style='background: #d1e7dd; border-left: 4px solid #198754; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                        <strong>Status: APPROVED ✅</strong>
                        " . ($remarks ? "<br><br><strong>Message from Scholarship Office:</strong><br>{$remarks}" : "") . "
                    </div>
                    <p>Please visit the scholarship office or log in to the portal to view your disbursement schedule.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost:8080/cainta_scholarship/student_login.php' 
                            style='background: #1A3A6B; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                            View My Application
                        </a>
                    </div>
                </div>
                <div style='background: #1A3A6B; padding: 15px; text-align: center;'>
                    <p style='color: #B5D4F4; margin: 0; font-size: 12px;'>
                        &copy; " . date('Y') . " Municipality of Cainta — Scholarship Office
                    </p>
                </div>
            </div>";
        } elseif($status == 'rejected') {
            $mail->Subject = 'Scholarship Application Status Update';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: #1A3A6B; padding: 20px; text-align: center;'>
                    <h2 style='color: white; margin: 0;'>Cainta Scholarship Program</h2>
                    <p style='color: #B5D4F4; margin: 5px 0 0;'>Municipality of Cainta, Rizal</p>
                </div>
                <div style='padding: 30px; background: #f9f9f9;'>
                    <h3 style='color: #1A3A6B;'>Dear {$to_name},</h3>
                    <p>We regret to inform you that your scholarship application has been <strong style='color: #dc3545;'>NOT APPROVED</strong> at this time.</p>
                    <div style='background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                        <strong>Status: NOT APPROVED ❌</strong>
                        " . ($remarks ? "<br><br><strong>Reason / Remarks:</strong><br>{$remarks}" : "") . "
                    </div>
                    <p>You may visit the scholarship office for further assistance or reapply in the next semester.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost:8080/cainta_scholarship/student_login.php' 
                            style='background: #1A3A6B; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                            View My Application
                        </a>
                    </div>
                </div>
                <div style='background: #1A3A6B; padding: 15px; text-align: center;'>
                    <p style='color: #B5D4F4; margin: 0; font-size: 12px;'>
                        &copy; " . date('Y') . " Municipality of Cainta — Scholarship Office
                    </p>
                </div>
            </div>";
        } elseif($status == 'incomplete') {
            $mail->Subject = 'Action Required: Incomplete Scholarship Application';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: #1A3A6B; padding: 20px; text-align: center;'>
                    <h2 style='color: white; margin: 0;'>Cainta Scholarship Program</h2>
                    <p style='color: #B5D4F4; margin: 5px 0 0;'>Municipality of Cainta, Rizal</p>
                </div>
                <div style='padding: 30px; background: #f9f9f9;'>
                    <h3 style='color: #1A3A6B;'>Dear {$to_name},</h3>
                    <p>Your scholarship application requires additional action. Some requirements are <strong style='color: #f0a500;'>INCOMPLETE</strong>.</p>
                    <div style='background: #fff3cd; border-left: 4px solid #f0a500; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                        <strong>Status: INCOMPLETE ⚠️</strong>
                        " . ($remarks ? "<br><br><strong>Missing Requirements:</strong><br>{$remarks}" : "") . "
                    </div>
                    <p>Please log in to the portal and complete your application requirements as soon as possible.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost:8080/cainta_scholarship/student_login.php' 
                            style='background: #1A3A6B; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                            Complete My Application
                        </a>
                    </div>
                </div>
                <div style='background: #1A3A6B; padding: 15px; text-align: center;'>
                    <p style='color: #B5D4F4; margin: 0; font-size: 12px;'>
                        &copy; " . date('Y') . " Municipality of Cainta — Scholarship Office
                    </p>
                </div>
            </div>";
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>