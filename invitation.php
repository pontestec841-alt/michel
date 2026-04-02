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

// Fetch settings
try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar configurações.");
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
            <p><strong>Data:</strong> <?php echo htmlspecialchars($settings['event_date']); ?></p>
            <p><strong>Hora:</strong> <?php echo htmlspecialchars($settings['event_time']); ?></p>
            <p><strong>Local:</strong> <?php echo htmlspecialchars($settings['event_location']); ?></p>

            <?php if ($giftSelected): ?>
                <p style="margin-top: 20px; font-style: italic; color: var(--secondary-color);">
                    Muito obrigado por escolher um presente da minha lista!
                </p>
            <?php endif; ?>

            <div style="margin-top: 30px;">
                <img src="assets/image3.jpg" alt="Decoração" style="width: 100%; max-width: 400px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            </div>

            <?php if ($settings['music_enabled'] && !empty($settings['music_url'])): ?>
                <div class="no-print" style="margin-top: 20px;">
                    <audio controls autoplay loop>
                        <source src="<?php echo htmlspecialchars($settings['music_url']); ?>" type="audio/mpeg">
                        Seu navegador não suporta o elemento de áudio.
                    </audio>
                </div>
            <?php endif; ?>

            <div class="no-print" style="margin-top: 30px;">
                <button onclick="window.print()" style="padding: 15px 30px; font-size: 1.2rem;">Salvar Convite (PDF/Imprimir)</button>
            </div>
        </div>
    </div>
</body>
</html>
