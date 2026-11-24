<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nature_energy');

// define('DB_HOST', 'localhost');
// define('DB_USER', 'u598458757_usr_3OKIMv4d');
// define('DB_PASS', '2bh38+!&YY!Q');
// define('DB_NAME', 'u598458757_db_3OKIMv4d');

// Mail configuration (for PHPMailer)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USER', 'developer@gohilinfotech.com');
define('MAIL_PASS', 'fmljjkikzopcrpet');
define('MAIL_PORT', 465);
define('MAIL_ENCRYPTION', 'ssl'); // port 465 uses SSL (not STARTTLS/587)
define('MAIL_FROM', 'developer@gohilinfotech.com');
define('MAIL_FROM_NAME', 'nature');

// Admin configuration
define('JWT_SECRET', 'nature_energy_secret_key_123'); // Simple secret for demo, in production use environment variables

// CORS Configuration
function applyCorsHeaders()
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedPatterns = [
        '/^https:\/\/(www\.)?natureenergy\.in$/i',
        '/^http:\/\/localhost(?::\d+)?$/i',
        '/^http:\/\/127\.0\.0\.1(?::\d+)?$/i',
    ];

    if ($origin !== '') {
        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                header("Access-Control-Allow-Origin: $origin");
                header('Vary: Origin');
                break;
            }
        }
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
}

applyCorsHeaders();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

function getDbConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    return $conn;
}
?>
