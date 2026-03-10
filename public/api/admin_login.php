<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Harap isi email dan password.']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // In a real app, use JWT here. For simplicity on shared hosting, we'll return a success state.
        // The frontend can store a flag in localStorage for this demonstration.
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil!',
            'user' => [
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email atau password salah.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan server: ' . $e->getMessage()]);
}
?>
