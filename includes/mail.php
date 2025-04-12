<?php
/**
 * 发送预订确认邮件
 */
function sendBookingConfirmationEmail($userEmail, $userName, $bookingDetails, $roomDetails) {
    $subject = 'Your Booking Confirmation - Luxury Hotel';
    
    // 创建HTML格式的邮件内容
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #0056b3; color: white; padding: 15px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f1f1f1; padding: 10px; text-align: center; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            table, th, td { border: 1px solid #ddd; }
            th, td { padding: 10px; text-align: left; }
            th { background-color: #f1f1f1; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Booking Confirmation</h1>
            </div>
            <div class="content">
                <p>Dear ' . $userName . ',</p>
                <p>Thank you for choosing Luxury Hotel. Your booking has been confirmed.</p>
                
                <h2>Booking Details</h2>
                <table>
                    <tr>
                        <th>Booking ID</th>
                        <td>' . substr($bookingDetails['id'], -8) . '</td>
                    </tr>
                    <tr>
                        <th>Room Type</th>
                        <td>' . $roomDetails['type'] . '</td>
                    </tr>
                    <tr>
                        <th>Check-in Date</th>
                        <td>' . date('F j, Y', strtotime($bookingDetails['check_in'])) . '</td>
                    </tr>
                    <tr>
                        <th>Check-out Date</th>
                        <td>' . date('F j, Y', strtotime($bookingDetails['check_out'])) . '</td>
                    </tr>
                    <tr>
                        <th>Number of Guests</th>
                        <td>' . $bookingDetails['guests'] . '</td>
                    </tr>
                    <tr>
                        <th>Total Price</th>
                        <td>$' . number_format($bookingDetails['total_price'], 2) . '</td>
                    </tr>
                </table>
                
                <p>If you need to make any changes to your reservation, please contact us as soon as possible.</p>
                <p>We look forward to welcoming you to Luxury Hotel!</p>
                
                <p>Best Regards,<br>Luxury Hotel Team</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // 邮件头信息
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Luxury Hotel <noreply@luxuryhotel.com>' . "\r\n";
    
    // 发送邮件
    $mailSent = mail($userEmail, $subject, $message, $headers);
    
    // 记录邮件发送情况到日志（可选）
    if (!$mailSent) {
        error_log("Failed to send confirmation email to $userEmail for booking " . $bookingDetails['id']);
    }
    
    return $mailSent;
}

/**
 * 发送预订取消确认邮件
 */
function sendBookingCancellationEmail($userEmail, $userName, $bookingDetails, $roomDetails) {
    $subject = 'Your Booking Cancellation - Luxury Hotel';
    
    // 创建HTML格式的邮件内容
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #dc3545; color: white; padding: 15px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f1f1f1; padding: 10px; text-align: center; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            table, th, td { border: 1px solid #ddd; }
            th, td { padding: 10px; text-align: left; }
            th { background-color: #f1f1f1; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Booking Cancellation</h1>
            </div>
            <div class="content">
                <p>Dear ' . $userName . ',</p>
                <p>Your booking with Luxury Hotel has been cancelled as requested.</p>
                
                <h2>Cancelled Booking Details</h2>
                <table>
                    <tr>
                        <th>Booking ID</th>
                        <td>' . substr($bookingDetails['id'], -8) . '</td>
                    </tr>
                    <tr>
                        <th>Room Type</th>
                        <td>' . $roomDetails['type'] . '</td>
                    </tr>
                    <tr>
                        <th>Check-in Date</th>
                        <td>' . date('F j, Y', strtotime($bookingDetails['check_in'])) . '</td>
                    </tr>
                    <tr>
                        <th>Check-out Date</th>
                        <td>' . date('F j, Y', strtotime($bookingDetails['check_out'])) . '</td>
                    </tr>
                </table>
                
                <p>If you did not request this cancellation or have any questions, please contact us immediately.</p>
                <p>We hope to welcome you at Luxury Hotel in the future.</p>
                
                <p>Best Regards,<br>Luxury Hotel Team</p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // 邮件头信息
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Luxury Hotel <noreply@luxuryhotel.com>' . "\r\n";
    
    // 发送邮件
    $mailSent = mail($userEmail, $subject, $message, $headers);
    
    // 记录邮件发送情况到日志（可选）
    if (!$mailSent) {
        error_log("Failed to send cancellation email to $userEmail for booking " . $bookingDetails['id']);
    }
    
    return $mailSent;
}
?> 