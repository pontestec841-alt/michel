<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['guest_id'])) {
    header("Location: index.php");
    exit();
}

$guestName = $_SESSION['guest_name'];
$guestId = $_SESSION['guest_id'];

// Check if they selected a gift to say thank you
$giftSelected = false;
try {
    $stmt = $pdo->prepare("SELECT name FROM gifts WHERE reserved_by = :guest_id LIMIT 1");
    $stmt->execute(['guest_id' => $guestId]);
    if ($stmt->rowCount() > 0) {
        $giftSelected = true;
    }
} catch (PDOException $e) {
    // Ignore error, just proceed
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Convite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container invitation-card" style="max-width: 800px; width: 95%;">
        <div class="invitation-content">
            <h1>Você está Convidado!</h1>
            <p style="font-size: 1.5rem; font-weight: 600;">Querido(a) <?php echo htmlspecialchars($guestName); ?>,</p>
            <p>A magia está no ar! Celebre comigo este dia muito especial.</p>
            <hr style="border: 1px solid var(--primary-color); margin: 20px 0;">
            <p><strong>Data:</strong> 15 de Novembro de 2024</p>
            <p><strong>Hora:</strong> 15:00 hs</p>
            <p><strong>Local:</strong> Jardim das Fadas (Rua Exemplo, 123)</p>

            <?php if ($giftSelected): ?>
                <p style="margin-top: 20px; font-style: italic; color: var(--secondary-color);">
                    Muito obrigado por escolher um presente da minha lista!
                </p>
            <?php endif; ?>

            <div style="margin-top: 30px;">
                <img src="assets/image3.jpg" alt="Decoração" style="width: 100%; max-width: 400px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            </div>

            <p style="margin-top: 30px; font-size: 0.9rem;">(Salve esta página ou tire um print do seu convite!)</p>
        </div>
    </div>
</body>
</html>
