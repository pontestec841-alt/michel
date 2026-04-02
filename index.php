<?php
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite de Aniversário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <img src="assets/image1.jpg" alt="Aniversariante" class="hero-image">
        <h1>Meu Aniversário!</h1>
        <p>Você está convidado para celebrar este dia mágico comigo.</p>
        <p>Por favor, confirme sua presença digitando seu nome abaixo:</p>

        <form action="rsvp.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="text" name="guest_name" placeholder="Digite seu nome" required>
            <button type="submit">Confirmar Presença</button>
        </form>
    </div>
</body>
</html>
