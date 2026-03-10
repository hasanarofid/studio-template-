<?php
header('Content-Type: application/json');
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$template_id = $_POST['template_id'] ?? '';
$amount = $_POST['amount'] ?? '';

if (empty($name) || empty($email) || empty($template_id) || empty($_FILES['receipt'])) {
    echo json_encode(['success' => false, 'message' => 'Harap isi semua data dan unggah bukti transfer.']);
    exit;
}

// Handle File Upload
$target_dir = "../uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$file_extension = pathinfo($_FILES["receipt"]["name"], PATHINFO_EXTENSION);
$new_filename = uniqid() . "." . $file_extension;
$target_file = $target_dir . $new_filename;

try {
    if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file)) {
        // Simple User Auto-Registration if not exists (for tracking orders)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, password_hash('temporary', PASSWORD_DEFAULT)]);
            $user_id = $pdo->lastInsertId();
        } else {
            $user_id = $user['id'];
        }

        // Get template name for email notification
        $stmt = $pdo->prepare("SELECT name FROM templates WHERE id = ?");
        $stmt->execute([$template_id]);
        $template = $stmt->fetch();
        $template_name = $template ? $template['name'] : 'Unknown Template';

        // Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, template_id, amount, status, payment_receipt) VALUES (?, ?, ?, 'pending', ?)");
        if ($stmt->execute([$user_id, $template_id, $amount, $new_filename])) {
            
            // Send Email Notification
            try {
                if (file_exists('mail_helper.php')) {
                    require_once 'mail_helper.php';
                    $config = require 'smtp_config.php';
                    
                    $subject = "Pesanan Baru: " . $template_name;
                    $body = "<h2>Pesanan Baru Diterima!</h2>
                             <p><b>User:</b> $name ($email)</p>
                             <p><b>Template:</b> " . $template_name . "</p>
                             <p><b>Jumlah:</b> Rp" . number_format($amount, 0, ',', '.') . "</p>
                             <p>Silakan cek panel admin untuk verifikasi bukti pembayaran.</p>";
                    
                    @send_email($config['admin_email'], $subject, $body);
                }
            } catch (Exception $e) {
                // Ignore email errors to prevent submission failure
            }

            echo json_encode(['success' => true, 'message' => 'Bukti pembayaran berhasil dikirim! Silakan tunggu verifikasi admin.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan ke database.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar ke folder uploads.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()]);
}
?>
