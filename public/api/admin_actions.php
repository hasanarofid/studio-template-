<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// In a real app, verify admin session/token here
$action = $_GET['action'] ?? '';

try {
    if ($action === 'get_orders') {
        $stmt = $pdo->query("SELECT o.*, u.name as user_name, u.email as user_email, t.name as template_name 
                             FROM orders o 
                             JOIN users u ON o.user_id = u.id 
                             JOIN templates t ON o.template_id = t.id 
                             ORDER BY o.created_at DESC");
        $orders = $stmt->fetchAll();
        echo json_encode(['success' => true, 'orders' => $orders]);
    } 
    elseif ($action === 'verify_payment') {
        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = $data['order_id'] ?? '';
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID diperlukan.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = 'verified' WHERE id = ?");
        $stmt->execute([$order_id]);

        // Notify User
        require_once 'mail_helper.php';
        $stmt_order = $pdo->prepare("SELECT o.*, u.email, t.name as template_name FROM orders o 
                                     JOIN users u ON o.user_id = u.id 
                                     JOIN templates t ON o.template_id = t.id 
                                     WHERE o.id = ?");
        $stmt_order->execute([$order_id]);
        $order = $stmt_order->fetch();

        if ($order) {
            $subject = "Pembayaran Diverifikasi - " . $order['template_name'];
            $body = "<h2>Konfirmasi Pembayaran</h2>
                     <p>Halo, pembayaran Anda untuk <b>" . $order['template_name'] . "</b> telah diverifikasi!</p>
                     <p>Tim kami akan segera menghubungi Anda untuk langkah selanjutnya.</p>";
            send_email($order['email'], $subject, $body);
        }

        echo json_encode(['success' => true, 'message' => 'Pembayaran telah diverifikasi! Email konfirmasi dikirim ke user.']);
    }
    elseif ($action === 'reject_payment') {
        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = $data['order_id'] ?? '';
        $reason = $data['reason'] ?? '';

        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID diperlukan.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $order_id]);

        // Notify User
        require_once 'mail_helper.php';
        $stmt_order = $pdo->prepare("SELECT o.*, u.email, t.name as template_name FROM orders o 
                                     JOIN users u ON o.user_id = u.id 
                                     JOIN templates t ON o.template_id = t.id 
                                     WHERE o.id = ?");
        $stmt_order->execute([$order_id]);
        $order = $stmt_order->fetch();

        if ($order) {
            $subject = "Pembayaran Ditolak - " . $order['template_name'];
            $body = "<h2>Pemberitahuan Pembayaran</h2>
                     <p>Mohon maaf, pembayaran Anda untuk <b>" . $order['template_name'] . "</b> ditolak.</p>
                     <p><b>Alasan:</b> $reason</p>
                     <p>Silakan lakukan pembayaran ulang atau hubungi admin.</p>";
            send_email($order['email'], $subject, $body);
        }

        echo json_encode(['success' => true, 'message' => 'Pembayaran ditolak. Email notifikasi dikirim ke user.']);
    }
    elseif ($action === 'save_template') {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? '';
        $category = $_POST['category'] ?? '';
        $preview_url = $_POST['preview_url'] ?? '';

        if (empty($name) || empty($price)) {
            echo json_encode(['success' => false, 'message' => 'Nama dan harga wajib diisi.']);
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE templates SET name=?, description=?, price=?, category=?, preview_url=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $category, $preview_url, $id]);
            echo json_encode(['success' => true, 'message' => 'Template berhasil diperbarui!']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO templates (name, description, price, category, preview_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category, $preview_url]);
            echo json_encode(['success' => true, 'message' => 'Template berhasil ditambahkan!']);
        }
    }
    elseif ($action === 'delete_template') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM templates WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Template berhasil dihapus.']);
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
