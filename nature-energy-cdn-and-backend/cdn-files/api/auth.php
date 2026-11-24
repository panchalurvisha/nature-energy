<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Auth.php';

Auth::checkApi();

echo json_encode(['success' => true, 'message' => 'Authenticated']);
