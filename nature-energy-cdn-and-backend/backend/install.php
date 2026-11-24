<?php
// Run this script once to set up the database
// Access via: /backend/install.php

require_once __DIR__ . '/config.php';

$conn = getDbConnection();

$sqls = [
    "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        product VARCHAR(255) DEFAULT NULL,
        message TEXT,
        status ENUM('new','responded','archived') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS mail_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contact_id INT NOT NULL,
        email_type ENUM('admin', 'customer') NOT NULL,
        recipient VARCHAR(255) NOT NULL,
        status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
        attempts INT DEFAULT 0,
        last_error TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        sent_at TIMESTAMP NULL,
        FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
    )"
];

$errors = [];
foreach ($sqls as $sql) {
    if (!$conn->query($sql)) {
        $errors[] = $conn->error . ' [SQL: ' . $sql . ']';
    }
}

// Check if admin already exists
$result = $conn->query("SELECT id FROM admins WHERE username = 'nature_admin'");
$adminCreated = false;

if ($result && $result->num_rows === 0) {
    $hash = password_hash('I^showRabbit@2026', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES ('nature_admin', ?)");
    $stmt->bind_param('s', $hash);
    if (!$stmt->execute()) {
        $errors[] = 'Failed to create admin user: ' . $stmt->error;
    } else {
        $adminCreated = true;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Nature Energy - Database Setup</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; max-width: 640px; margin: 60px auto; padding: 20px; background: #f5f7fa; color: #354653; }
  .card { background: #fff; border-radius: 14px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
  h1 { color: #1A2533; font-size: 22px; margin: 0 0 6px; }
  p { color: #5B707E; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
  .success { color: #276749; background: #F0FFF4; border: 1px solid #9AE6B4; border-radius: 8px; padding: 10px 16px; font-size: 13px; margin-bottom: 10px; }
  .error { color: #9B2C2C; background: #FFF5F5; border: 1px solid #FEB2B2; border-radius: 8px; padding: 10px 16px; font-size: 13px; margin-bottom: 10px; }
  .cred { background: #EBF8FD; border: 1px solid #BEE3F8; border-radius: 8px; padding: 14px 16px; font-size: 13px; color: #1A5A7A; margin-top: 16px; }
  .cred code { font-family: monospace; font-weight: 700; font-size: 15px; }
  a { color: #078DA4; font-weight: 600; text-decoration: none; }
</style>
</head>
<body>
<div class="card">
  <h1>Nature Energy - Database Setup</h1>
  <p>Setting up the database and tables for the contact management system.</p>

  <?php if (empty($errors)): ?>
    <div class="success">Database connected and tables created successfully.</div>
    <?php if ($adminCreated): ?>
      <div class="success">Admin user created successfully.</div>
      <div class="cred">
        <strong>Default Admin Credentials</strong><br>
        Username: <code>nature_admin</code><br>
        Password: <code>I^showRabbit@2026</code><br>
        <small>Please change these after your first login.</small>
      </div>
    <?php else: ?>
      <div class="success">Admin user already exists - not re-created.</div>
    <?php endif; ?>
  <?php else: ?>
    <?php foreach ($errors as $e): ?>
      <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <p style="margin-top:24px;">
    <a href="/admin">Go to Admin Panel</a> | 
    <a href="/contact">Contact Page</a>
  </p>
  <p style="font-size:12px;color:#aaa;margin-top:8px;">
    Delete or restrict access to this file in production.
  </p>
</div>
</body>
</html>
