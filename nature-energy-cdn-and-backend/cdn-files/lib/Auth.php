<?php

class Auth {
    public static function checkApi() {
        // Get Authorization header from multiple sources
        $headers = [];
        $authHeader = '';
        
        // Try getallheaders() first
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        
        // Fallback to $_SERVER
        if (empty($authHeader)) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        }
        
        $config = require __DIR__ . '/../config/config.php';

        if (
            empty($authHeader) ||
            $authHeader !== 'Bearer ' . $config['auth_token']
        ) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    public static function checkAdmin() {
        session_start();
        if (!isset($_SESSION['admin'])) {
            $config = require __DIR__ . '/../config/config.php';
            header('Location: ' . $config['base_url'] . '/admin/login.php');
            exit;
        }

    }
}
