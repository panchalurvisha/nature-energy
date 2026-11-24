<?php

return [
    'base_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(str_replace(['/admin', '/api', '/config', '/lib'], '', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\'),

    'upload_path' => __DIR__ . '/../uploads/',


    'auth_token' => 'secure_cdn_token_123456',

    'admin' => [
        'username' => 'admin',
        'password' => password_hash('admin@123', PASSWORD_BCRYPT)
    ],

    'max_size' => 200 * 1024 * 1024, // 200MB

    'allowed' => [
        'image' => ['jpg', 'jpeg', 'png', 'webp'],
        'video' => ['mp4', 'webm']
    ]
];
