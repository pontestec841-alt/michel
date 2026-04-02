<?php
session_start();
require_once 'db.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Check if user is logged in (has RSVP'd)
if (!isset($_SESSION['guest_id'])) {
    header("Location: index.php");
    exit();
}

$guestName = $_SESSION['guest_name'];

// Fetch available gifts
try {
    $stmt = $pdo->query("SELECT id, name FROM gifts WHERE reserved_by IS NULL");
    $availableGifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar presentes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Presentes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Obrigado por confirmar, <?php echo htmlspecialchars($guestName); ?>!</h2>
        <p>Abaixo está uma lista de sugestões de presentes. Não é obrigatório!</p>
        <p>Se desejar nos presentear com algo da lista, basta selecionar a opção abaixo. O item será retirado da lista para os outros convidados.</p>

        <?php if (count($availableGifts) > 0): ?>
            <ul class="gift-list">
                <?php foreach ($availableGifts as $gift): ?>
                    <li class="gift-item">
                        <span><?php echo htmlspecialchars($gift['name']); ?></span>
                        <form action="select_gift.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="gift_id" value="<?php echo $gift['id']; ?>">
                            <button type="submit">Escolher Presente</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><strong>A lista de sugestões já foi toda preenchida. Muito obrigado pelo carinho!</strong></p>
        <?php endif; ?>

        <a href="invitation.php" class="skip-link">Pular e ver meu convite</a>
    </div>
</body>
</html>
