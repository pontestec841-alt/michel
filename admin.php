<?php
session_start();
require_once 'db.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Very simple authentication for admin
$admin_password = getenv('ADMIN_PASS') ?: 'admin123';

if (isset($_POST['admin_login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = "Senha incorreta.";
    }
}

if (isset($_POST['admin_logout'])) {
    unset($_SESSION['admin_logged_in']);
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Login Admin</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container" style="max-width: 400px;">
            <h2>Acesso Restrito</h2>
            <?php if(isset($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
            <form action="admin.php" method="POST">
                <input type="password" name="password" placeholder="Senha do Administrador" required>
                <button type="submit" name="admin_login">Entrar</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Ensure POST requests have valid CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['admin_login']) && !isset($_POST['admin_logout'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
}

// Handle updating settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    try {
        $stmt = $pdo->prepare("UPDATE settings SET event_date = :date, event_time = :time, event_location = :loc, music_url = :url, music_enabled = :enabled WHERE id = 1");
        $stmt->execute([
            'date' => $_POST['event_date'],
            'time' => $_POST['event_time'],
            'loc'  => $_POST['event_location'],
            'url'  => $_POST['music_url'],
            'enabled' => isset($_POST['music_enabled']) ? 1 : 0
        ]);
        $success_msg = "Configurações atualizadas com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao atualizar configurações: " . $e->getMessage();
    }
}

// Handle deleting a guest
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_guest_id'])) {
    $deleteId = $_POST['delete_guest_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM guests WHERE id = :id");
        $stmt->execute(['id' => $deleteId]);
    } catch (PDOException $e) {
        $error = "Erro ao deletar convidado: " . $e->getMessage();
    }
}

// Handle editing a guest count
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_guest_id'])) {
    $editId = $_POST['edit_guest_id'];
    $newCount = (int)$_POST['new_guest_count'];
    try {
        $stmt = $pdo->prepare("UPDATE guests SET guest_count = :count WHERE id = :id");
        $stmt->execute(['count' => $newCount, 'id' => $editId]);
    } catch (PDOException $e) {
        $error = "Erro ao editar convidado: " . $e->getMessage();
    }
}

// Handle adding a new gift
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_gift'])) {
    $newGiftName = trim($_POST['new_gift']);
    if (!empty($newGiftName)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO gifts (name) VALUES (:name)");
            $stmt->execute(['name' => $newGiftName]);
        } catch (PDOException $e) {
            $error = "Erro ao adicionar presente: " . $e->getMessage();
        }
    }
}

// Handle deleting a gift
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gift_id'])) {
    $deleteId = $_POST['delete_gift_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM gifts WHERE id = :id");
        $stmt->execute(['id' => $deleteId]);
    } catch (PDOException $e) {
        $error = "Erro ao deletar presente: " . $e->getMessage();
    }
}

// Fetch settings
try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar configurações: " . $e->getMessage());
}

// Fetch all guests
try {
    $stmt = $pdo->query("SELECT * FROM guests ORDER BY created_at DESC");
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total attendees
    $total_attendees = 0;
    foreach($guests as $g) {
        $total_attendees += $g['guest_count'];
    }
} catch (PDOException $e) {
    die("Erro ao buscar convidados: " . $e->getMessage());
}

// Fetch all gifts with the name of the guest who reserved them (if any)
try {
    $stmt = $pdo->query("
        SELECT gifts.id, gifts.name AS gift_name, guests.name AS guest_name
        FROM gifts
        LEFT JOIN guests ON gifts.reserved_by = guests.id
        ORDER BY gifts.id DESC
    ");
    $gifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar presentes: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Aniversário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Painel de Administração</h1>
            <form action="admin.php" method="POST" style="margin:0;">
                <button type="submit" name="admin_logout" style="padding: 8px 15px; font-size: 0.9rem;">Sair</button>
            </form>
        </div>

        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <?php if(isset($success_msg)) echo "<p style='color:green;'>$success_msg</p>"; ?>

        <div style="background: #fff0f5; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <h2>Configurações do Evento</h2>
            <form action="admin.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div style="display:flex; flex-direction:column;">
                    <label>Data:</label>
                    <input type="text" name="event_date" value="<?php echo htmlspecialchars($settings['event_date']); ?>" required>
                </div>
                <div style="display:flex; flex-direction:column;">
                    <label>Hora:</label>
                    <input type="text" name="event_time" value="<?php echo htmlspecialchars($settings['event_time']); ?>" required>
                </div>
                <div style="grid-column: 1 / -1; display:flex; flex-direction:column;">
                    <label>Local:</label>
                    <input type="text" name="event_location" value="<?php echo htmlspecialchars($settings['event_location']); ?>" required>
                </div>
                <div style="grid-column: 1 / -1; display:flex; flex-direction:column;">
                    <label>URL da Música (Opcional):</label>
                    <input type="text" name="music_url" value="<?php echo htmlspecialchars($settings['music_url']); ?>">
                </div>
                <div style="grid-column: 1 / -1; display:flex; align-items:center; gap:10px;">
                    <input type="checkbox" name="music_enabled" value="1" <?php echo $settings['music_enabled'] ? 'checked' : ''; ?> style="width: auto;">
                    <label>Ativar música no convite</label>
                </div>
                <div style="grid-column: 1 / -1;">
                    <button type="submit" name="update_settings" style="width:100%;">Salvar Configurações</button>
                </div>
            </form>
        </div>

        <h2>Convidados Confirmados</h2>
        <p style="font-weight:bold; color:var(--secondary-color);">Total de Pessoas: <?php echo $total_attendees; ?> (Famílias: <?php echo count($guests); ?>)</p>

        <?php if (count($guests) > 0): ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Qtd. Pessoas</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guests as $guest): ?>
                            <tr>
                                <td><?php echo $guest['id']; ?></td>
                                <td><?php echo htmlspecialchars($guest['name']); ?></td>
                                <td>
                                    <form action="admin.php" method="POST" style="margin:0; display:flex; gap:5px; align-items:center;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="edit_guest_id" value="<?php echo $guest['id']; ?>">
                                        <input type="number" name="new_guest_count" value="<?php echo $guest['guest_count']; ?>" min="1" style="width:60px; padding:5px;">
                                        <button type="submit" style="padding: 5px 10px; font-size: 0.8rem;">Salvar</button>
                                    </form>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($guest['created_at'])); ?></td>
                                <td>
                                    <form action="admin.php" method="POST" style="margin:0; padding:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="delete_guest_id" value="<?php echo $guest['id']; ?>">
                                        <button type="submit" style="padding: 5px 10px; font-size: 0.8rem; background: #ff4d4d;" onclick="return confirm('Tem certeza que deseja deletar este convidado?');">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Nenhum convidado confirmado ainda.</p>
        <?php endif; ?>

        <hr style="margin: 40px 0;">

        <h2>Gerenciar Lista de Presentes</h2>

        <form action="admin.php" method="POST" style="flex-direction: row; justify-content: center;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="text" name="new_gift" placeholder="Nome do novo presente" required style="width: 60%;">
            <button type="submit" style="padding: 12px 20px;">Adicionar</button>
        </form>

        <?php if (count($gifts) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Presente</th>
                        <th>Status / Reservado por</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gifts as $gift): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($gift['gift_name']); ?></td>
                            <td>
                                <?php if ($gift['guest_name']): ?>
                                    <span style="color: var(--secondary-color); font-weight: bold;">
                                        Reservado por: <?php echo htmlspecialchars($gift['guest_name']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: green;">Disponível</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="admin.php" method="POST" style="margin:0; padding:0;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="delete_gift_id" value="<?php echo $gift['id']; ?>">
                                    <button type="submit" style="padding: 5px 10px; font-size: 0.8rem; background: #ff4d4d;" onclick="return confirm('Tem certeza que deseja deletar este presente?');">Deletar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>A lista de presentes está vazia.</p>
        <?php endif; ?>

    </div>
</body>
</html>
