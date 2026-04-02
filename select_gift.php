<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['guest_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['gift_id'])) {
        $giftId = $_POST['gift_id'];
        $guestId = $_SESSION['guest_id'];

        try {
            // Only reserve if it's currently unreserved
            $stmt = $pdo->prepare("UPDATE gifts SET reserved_by = :guest_id WHERE id = :gift_id AND reserved_by IS NULL");
            $stmt->execute([
                'guest_id' => $guestId,
                'gift_id' => $giftId
            ]);

            header("Location: invitation.php");
            exit();
        } catch (PDOException $e) {
            die("Erro ao selecionar presente: " . $e->getMessage());
        }
    }
}
header("Location: gifts.php");
exit();
?>
