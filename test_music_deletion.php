<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT music_url FROM settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Current music_url: " . $settings['music_url'] . "\n";
