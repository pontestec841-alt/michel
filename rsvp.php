<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    if (!empty($_POST['guest_name'])) {
        $guestName = trim($_POST['guest_name']);

        try {
            $stmt = $pdo->prepare("INSERT INTO guests (name) VALUES (:name)");
            $stmt->execute(['name' => $guestName]);

            $guestId = $pdo->lastInsertId();

            // Store guest info in session
            $_SESSION['guest_id'] = $guestId;
            $_SESSION['guest_name'] = $guestName;

            // Redirect to gifts page
            header("Location: gifts.php");
            exit();
        } catch (PDOException $e) {
            die("Erro ao registrar presença: " . $e->getMessage());
        }
    }
}
header("Location: index.php");
exit();
?>
