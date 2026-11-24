<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Auth.php';

Auth::checkApi();

$config = require __DIR__ . '/../config/config.php';

$type = $_GET['type'] ?? 'image';
$dir = $config['upload_path'] . $type . 's/';

if (!is_dir($dir)) {
    echo json_encode(['files' => []]);
    exit;
}

$files = [];
$items = scandir($dir);

foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    
    $path = $dir . $item;
    if (is_file($path)) {
        $files[] = [
            'name' => $item,
            'url' => $config['base_url'] . '/uploads/' . $type . 's/' . $item,
            'size' => filesize($path),
            'modified' => filemtime($path)
        ];
    }
}

echo json_encode(['files' => $files]);
