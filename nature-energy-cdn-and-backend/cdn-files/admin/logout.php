<?php
session_start();
session_destroy();
$config = require __DIR__ . '/../config/config.php';
header('Location: ' . $config['base_url'] . '/admin/login.php');

exit;
?>
