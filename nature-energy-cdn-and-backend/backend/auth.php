<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password required.']);
        exit;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$admin || !password_verify($password, $admin['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }

    // Generate simple signed token: base64(payload).signature
    $payload = base64_encode(json_encode([
        'id'       => $admin['id'],
        'username' => $admin['username'],
        'exp'      => time() + 86400 // 24h expiry
    ]));
    $signature = hash_hmac('sha256', $payload, JWT_SECRET);
    $token = $payload . '.' . $signature;

    echo json_encode([
        'success'  => true,
        'token'    => $token,
        'username' => $admin['username']
    ]);
    exit;
}

if ($action === 'verify') {
    $token = $_GET['token'] ?? '';
    echo json_encode(verifyToken($token));
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);

// ----- Helper -----
function verifyToken($token) {
    if (empty($token)) return ['valid' => false];

    $parts = explode('.', $token);
    if (count($parts) !== 2) return ['valid' => false];

    [$payload, $signature] = $parts;
    $expected = hash_hmac('sha256', $payload, JWT_SECRET);

    if (!hash_equals($expected, $signature)) return ['valid' => false];

    $data = json_decode(base64_decode($payload), true);
    if (!$data || $data['exp'] < time()) return ['valid' => false];

    return ['valid' => true, 'username' => $data['username'], 'id' => $data['id']];
}
?>
