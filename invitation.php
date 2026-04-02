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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
                    <audio id="bg-music" autoplay loop style="display:none;">
                        <source src="<?php echo htmlspecialchars($settings['music_url']); ?>" type="audio/mpeg">
                        Seu navegador não suporta o elemento de áudio.
                    </audio>
                    <button id="mute-btn" onclick="toggleMute()" style="padding: 10px; border-radius: 50%; width: 50px; height: 50px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto; cursor: pointer;">
                        🔊
                    </button>
                </div>
            <?php endif; ?>

            <div class="no-print" id="action-buttons" style="margin-top: 30px;">
                <button onclick="downloadImage()" style="padding: 15px 30px; font-size: 1.2rem; cursor: pointer; width: 100%;">Salvar Convite</button>
            </div>
        </div>
    </div>

    <script>
        function toggleMute() {
            const music = document.getElementById('bg-music');
            const btn = document.getElementById('mute-btn');
            if (music.muted) {
                music.muted = false;
                btn.innerHTML = '🔊';
                // Try to play if it was blocked by autoplay policy
                music.play().catch(e => console.log("Autoplay prevented:", e));
            } else {
                music.muted = true;
                btn.innerHTML = '🔇';
            }
        }

        // Attempt to start playing on first interaction if autoplay was blocked
        document.body.addEventListener('click', function() {
            const music = document.getElementById('bg-music');
            if (music && music.paused) {
                music.play().catch(e => console.log("Autoplay prevented:", e));
            }
        }, { once: true });

        function downloadImage() {
            // Temporarily hide elements we don't want in the image (like the button and audio)
            const noPrintElements = document.querySelectorAll('.no-print');
            noPrintElements.forEach(el => el.style.display = 'none');

            // Get the card element
            const cardElement = document.querySelector('.invitation-card');

            // Use html2canvas to capture the element
            html2canvas(cardElement, {
                scale: 2, // Higher resolution
                useCORS: true, // Allow cross-origin images to be loaded
                backgroundColor: null // Keep transparent background if any, or captures what is seen
            }).then(canvas => {
                // Restore the elements
                noPrintElements.forEach(el => el.style.display = '');

                // Create a download link and click it
                const link = document.createElement('a');
                link.download = 'meu_convite.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(err => {
                console.error("Erro ao gerar a imagem: ", err);
                alert("Ocorreu um erro ao gerar a imagem do convite. Tente novamente.");
                // Restore the elements in case of error
                noPrintElements.forEach(el => el.style.display = '');
            });
        }
    </script>
</body>
</html>
