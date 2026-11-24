<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/ImageCompressor.php';

Auth::checkApi();

$config = require __DIR__ . '/../config/config.php';

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'File missing']);
    exit;
}

$type = $_POST['type'] ?? 'image';
$file = $_FILES['file'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $config['allowed'][$type] ?? [])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

if ($file['size'] > $config['max_size']) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large']);
    exit;
}

$dir = $config['upload_path'] . $type . 's/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$name = uniqid() . '.' . $ext;
$path = $dir . $name;

if ($type === 'image') {
    ImageCompressor::compress($file['tmp_name'], $path);
} else {
    move_uploaded_file($file['tmp_name'], $path);
}

$url = $config['base_url'] . '/uploads/' . $type . 's/' . $name;

echo json_encode([
    'success' => true,
    'url' => $url
]);
