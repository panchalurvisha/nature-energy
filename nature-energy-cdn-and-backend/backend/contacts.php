<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// --- Verify token on every request ---
function requireAuth() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: No token']);
        exit;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid token format']);
        exit;
    }

    [$payload, $signature] = $parts;
    $expected = hash_hmac('sha256', $payload, JWT_SECRET);

    if (!hash_equals($expected, $signature)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Bad signature']);
        exit;
    }

    $data = json_decode(base64_decode($payload), true);
    if (!$data || $data['exp'] < time()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Token expired']);
        exit;
    }

    return $data;
}

$admin = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

$conn = getDbConnection();

if ($action === 'list' && $method === 'GET') {
    // List contacts with optional filters
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = 15;
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];
    $types  = '';

    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    if ($search) {
        $where[] = "(name LIKE ? OR company LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $s = "%$search%";
        $params = array_merge($params, [$s, $s, $s, $s]);
        $types .= 'ssss';
    }

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total count
    $countSql = "SELECT COUNT(*) as total FROM contacts $whereStr";
    $countStmt = $conn->prepare($countSql);
    if ($params) $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    // Actual data
    $sql = "SELECT * FROM contacts $whereStr ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $allParams = array_merge($params, [$limit, $offset]);
    $allTypes  = $types . 'ii';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($allTypes, ...$allParams);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'data'    => $rows,
        'total'   => (int)$total,
        'page'    => $page,
        'pages'   => ceil($total / $limit)
    ]);

} elseif ($action === 'update_status' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id     = (int)($data['id'] ?? 0);
    $status = $data['status'] ?? '';

    if (!$id || !in_array($status, ['new', 'responded', 'archived'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE contacts SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Status updated']);

} elseif ($action === 'delete' && $method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Contact deleted']);

} elseif ($action === 'stats' && $method === 'GET') {
    $totalRow      = $conn->query("SELECT COUNT(*) as c FROM contacts")->fetch_assoc();
    $newRow        = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE status='new'")->fetch_assoc();
    $respondedRow  = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE status='responded'")->fetch_assoc();
    $todayRow      = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE DATE(created_at) = CURDATE()")->fetch_assoc();
    
    // Get pending mail counts
    $pendingMailRow = $conn->query("SELECT COUNT(*) as c FROM mail_queue WHERE status = 'pending'")->fetch_assoc();
    
    $conn->close();

    echo json_encode([
        'success'   => true,
        'total'     => (int)$totalRow['c'],
        'new'       => (int)$newRow['c'],
        'responded' => (int)$respondedRow['c'],
        'today'     => (int)$todayRow['c'],
        'pending_mails' => (int)$pendingMailRow['c']
    ]);

} elseif ($action === 'process_queue' && $method === 'POST') {
    // Include the queue processor script
    ob_start();
    include __DIR__ . '/process_mail_queue.php';
    $output = ob_get_clean();
    
    // The processor already outputs JSON if not CLI
    echo $output;
    exit;

} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Not found']);
}
?>
