<?php
require_once 'db.php';

try {
    // Create guests table
    $pdo->exec("CREATE TABLE IF NOT EXISTS guests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create gifts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS gifts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        reserved_by INT DEFAULT NULL,
        FOREIGN KEY (reserved_by) REFERENCES guests(id) ON DELETE SET NULL
    )");

    // Insert some default gifts if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM gifts");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $defaultGifts = [
            'Boneca', 'Carrinho', 'Quebra-cabeça', 'Livro Infantil', 'Roupinha', 'Urso de Pelúcia', 'Patinete', 'Kit de Desenho'
        ];

        $stmt = $pdo->prepare("INSERT INTO gifts (name) VALUES (:name)");
        foreach ($defaultGifts as $gift) {
            $stmt->execute(['name' => $gift]);
        }
        echo "Default gifts inserted.\n";
    }

    echo "Database setup completed successfully.\n";

} catch (PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
}
?>
