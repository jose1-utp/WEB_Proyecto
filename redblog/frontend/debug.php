<?php
// frontend/debug.php
session_start();
echo "<pre>";
echo "=== DEBUG REDBLOG ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: " . print_r($_SESSION, true) . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current File: " . __FILE__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "=== ARCHIVOS ===\n";

function checkFile($path, $description) {
    $exists = file_exists($path) ? "✅ EXISTE" : "❌ NO EXISTE";
    echo "$description: $exists ($path)\n";
}

checkFile(__DIR__ . '/js/script.js', 'script.js');
checkFile(__DIR__ . '/css/style.css', 'style.css');
checkFile(__DIR__ . '/includes/navbar.php', 'navbar.php');
checkFile(__DIR__ . '/logout.php', 'logout.php');
checkFile(__DIR__ . '/../backend/config/database.php', 'database.php');

echo "</pre>";
?>