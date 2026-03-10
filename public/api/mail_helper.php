<?php
// mail_helper.php

function send_email($to, $subject, $message_body) {
    $config = require 'smtp_config.php';
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . $config['from_name'] . " <" . $config['from_email'] . ">" . "\r\n";

    // Standard PHP mail() often works better on shared hosting than raw SMTP if not configured.
    // If you need raw SMTP (PHPMailer style), you'd usually install a library via composer,
    // but for shared hosting simplicity, we use mail() with proper headers.
    
    $full_message = "
    <html>
    <head>
        <style>
            body { font-family: sans-serif; line-height: 1.6; color: #333; }
            .container { padding: 20px; border: 1px solid #eee; border-radius: 8px; max-width: 600px; margin: auto; }
            .footer { font-size: 0.8em; color: #999; margin-top: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            $message_body
            <div class='footer'>
                &copy; 2026 Studio Hasanarofid. All rights reserved.
            </div>
        </div>
    </body>
    </html>";

    return mail($to, $subject, $full_message, $headers);
}
?>
