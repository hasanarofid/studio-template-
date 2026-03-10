<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email dan password wajib diisi.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Return user data (excluding password)
        unset($user['password']);
        echo json_encode([
            'success' => true, 
            'message' => 'Login berhasil!', 
            'user' => $user,
            'token' => 'dummy-token-' . bin2hex(random_bytes(16)) // Simple token for compatibility
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email atau password salah.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
