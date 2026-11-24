<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Fallback to POST data
if (!$data) {
    $data = $_POST;
}

$name    = trim($data['name'] ?? '');
$company = trim($data['company'] ?? '');
$phone   = trim($data['phone'] ?? '');
$email   = trim($data['email'] ?? '');
$product = trim($data['product'] ?? '');
$message = trim($data['message'] ?? '');

if (empty($name) || empty($company) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Name, company, and phone are required.']);
    exit;
}

// --- Save to DB ---
$conn = getDbConnection();
$stmt = $conn->prepare("INSERT INTO contacts (name, company, phone, email, product, message) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssss', $name, $company, $phone, $email, $product, $message);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    $conn->close();
    exit;
}
$insertId = $stmt->insert_id;
$stmt->close();

// --- Queue Emails ---
// 1. Admin Notification Queue
$adminEmail = MAIL_USER;
$stmtAdmin = $conn->prepare("INSERT INTO mail_queue (contact_id, email_type, recipient) VALUES (?, 'admin', ?)");
$stmtAdmin->bind_param('is', $insertId, $adminEmail);
$stmtAdmin->execute();
$stmtAdmin->close();

// 2. Customer Auto-Reply Queue (if email provided)
if (!empty($email)) {
    $stmtCustomer = $conn->prepare("INSERT INTO mail_queue (contact_id, email_type, recipient) VALUES (?, 'customer', ?)");
    $stmtCustomer->bind_param('is', $insertId, $email);
    $stmtCustomer->execute();
    $stmtCustomer->close();
}

$conn->close();

echo json_encode([
    'success'   => true,
    'message'   => 'Your inquiry has been submitted successfully. We will get back to you within 24 hours.',
    'id'        => $insertId,
    'queued'    => true
]);
?>
