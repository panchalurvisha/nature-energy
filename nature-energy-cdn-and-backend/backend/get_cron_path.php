<?php
// Upload this file to your Hostinger server and visit it in your browser.
// It will print the exact path you need to paste into the Hostinger Cron Jobs panel.

echo "<h2>Hostinger Cron Job Paths</h2>";

echo "<h3>1. PHP Command (Recommended)</h3>";
echo "If you are running a file via PHP in Hostinger's Cron Jobs panel, your full absolute path to this specific file is:<br>";
echo "<code style='background:#f4f4f4; padding:5px; border-radius:3px; display:inline-block; margin-top:5px;'><strong>php -q " . __FILE__ . "</strong></code><br><br>";

echo "To find the path for another file in the same directory (like cron.php or process_queue.php), replace <b>get_cron_path.php</b> with the name of your file:<br>";
echo "<code style='background:#f4f4f4; padding:5px; border-radius:3px; display:inline-block; margin-top:5px;'><strong>php -q " . str_replace("get_cron_path.php", "your_file_name.php", __FILE__) . "</strong></code><br><br>";

echo "<hr>";

echo "<h3>2. WGET / CURL Command (Alternative)</h3>";
echo "Alternatively, you can just ping the direct URL if you're executing it over the web:<br>";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$fullUrl = $protocol . "://" . $host . $uri;

echo "<code style='background:#f4f4f4; padding:5px; border-radius:3px; display:inline-block; margin-top:5px;'><strong>wget -q -O - " . $fullUrl . " >/dev/null 2>&1</strong></code><br><br>";
?>
