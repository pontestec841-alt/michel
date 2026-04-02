<?php
require_once 'db.php';

try {
    // Add guest_count to guests table
    $pdo->exec("ALTER TABLE guests ADD COLUMN guest_count INT DEFAULT 1");
    echo "Added guest_count to guests table.\n";
} catch (PDOException $e) {
    // Ignore error if column already exists
    echo "guest_count might already exist.\n";
}

try {
    // Create settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT PRIMARY KEY DEFAULT 1,
        event_date VARCHAR(255) DEFAULT '15 de Novembro de 2024',
        event_time VARCHAR(255) DEFAULT '15:00 hs',
        event_location VARCHAR(255) DEFAULT 'Jardim das Fadas (Rua Exemplo, 123)',
        music_url VARCHAR(255) DEFAULT '',
        music_enabled TINYINT(1) DEFAULT 0
    )");

    // Ensure default settings exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (id) VALUES (1)");
    }
    echo "Settings table created/verified.\n";

} catch (PDOException $e) {
    die("Error updating database schema: " . $e->getMessage());
}
?>