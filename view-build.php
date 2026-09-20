<?php
session_start();
require_once 'config.php';
require_once 'funkcje.php';

// Pobierz ID białku z URL
$bild_id = $_GET['id'] ?? null;

if (!$bild_id) {
    header('Location: index.html');
    exit;
}

$bild = pobierz_bild_po_id($bild_id, $pdo);

if (!$bild) {
    header('Location: index.html');
    exit;
}

$komentarze = pobierz_komentarze($bild_id, $pdo);

// Obsługiwanie akcji (komentarze, polubienia, zapisywanie)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && czy_zalogowany()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_comment') {
        $wynik = dodaj_komentarz(
            $bild_id,
            $_POST['tresc_komentarza'] ?? '',
            $_POST['ocena'] ?? 1,
            $pdo
        );
        $message = $wynik['message'];
        $message_type = $wynik['success'] ? 'success' : 'error';
        
        if ($wynik['success']) {
            // Odśwież komentarze
            $komentarze = pobierz_komentarze($bild_id, $pdo);
            // Odśwież białek
            $bild = pobierz_bild_po_id($bild_id, $pdo);
        }
    } elseif ($action === 'like_build') {
        $wynik = polub_bild($bild_id, $pdo);
        $message = $wynik['message'];
        $message_type = $wynik['success'] ? 'success' : 'error';
        // Odśwież białek
        $bild = pobierz_bild_po_id($bild_id, $pdo);
    } elseif ($action === 'save_build') {
        $wynik = zapisz_bild($bild_id, $pdo);
        $message = $wynik['message'];
        $message_type = $wynik['success'] ? 'success' : 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bild['nazwa_bildu']); ?> - League Guide</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .build-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0084ff;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .build-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .build-header h1 {
            margin: 0 0 20px 0;
            font-size: 36px;
        }

        .build-meta {
            display: flex;
            gap: 30px;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .build-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .build-author {
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .build-author a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .build-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.3);
            color: white;
            border: 1px solid white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .action-btn.primary {
            background: #0084ff;
            border-color: #0084ff;
        }

        .action-btn.primary:hover {
            background: #0073e6;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 24px;
            border-bottom: 2px solid #0084ff;
            padding-bottom: 10px;
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .item-card {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .item-card label {
            display: block;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .item-card .item-name {
            color: #333;
            font-weight: 600;
            font-size: 16px;
        }

        .description {
            background: #f9f9f9;
            padding: 20px;
            border-left: 4px solid #0084ff;
            border-radius: 5px;
            color: #555;
            line-height: 1.6;
            font-size: 16px;
        }

        .comment-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .comment-form h3 {
            margin-top: 0;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #0084ff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #0073e6;
        }

        .comment {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #0084ff;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .comment-author {
            font-weight: 600;
            color: #333;
        }

        .comment-date {
            color: #999;
            font-size: 14px;
        }

        .comment-rating {
            margin-bottom: 10px;
            color: #ff9800;
            font-weight: 600;
        }

        .comment-text {
            color: #555;
            line-height: 1.6;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats {
            display: flex;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-number {
            font-weight: 600;
            color: #0084ff;
            font-size: 18px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .no-comments {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="build-container">
        <a href="index.html" class="back-link">← Powrót do strony głównej</a>

        <div class="build-header">
            <h1><?php echo htmlspecialchars($bild['nazwa_bildu']); ?></h1>
            
            <div class="build-meta">
                <span>📍 Rola: <?php echo htmlspecialchars($bild['rola']); ?></span>
                <span>👤 Postać: <?php echo htmlspecialchars($bild['postac']); ?></span>
            </div>

            <div class="build-author">
                Autor: <a href="#"><?php echo htmlspecialchars($bild['nazwa_uzytkownika']); ?></a>
            </div>

            <div class="build-actions">
                <?php if (czy_zalogowany()): ?>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="like_build">
                        <button type="submit" class="action-btn primary">👍 Polub (<?php echo $bild['liczba_polubien']; ?>)</button>
                    </form>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="save_build">
                        <button type="submit" class="action-btn">💾 Zapisz</button>
                    </form>
                <?php else: ?>
                    <a href="login-register.php" class="action-btn primary">Zaloguj się aby polubić</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($bild['opis']): ?>
            <div class="section">
                <h2>Opis</h2>
                <div class="description">
                    <?php echo nl2br(htmlspecialchars($bild['opis'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>Itemy</h2>
            <div class="items-grid">
                <?php if ($bild['boot_item']): ?>
                    <div class="item-card">
                        <label>Buty</label>
                        <div class="item-name"><?php echo htmlspecialchars($bild['boot_item']); ?></div>
                    </div>
                <?php endif; ?>
                <?php for ($i = 1; $i <= 5; $i++): 
                    $item_key = "item$i";
                    if ($bild[$item_key]):
                ?>
                    <div class="item-card">
                        <label>Item <?php echo $i; ?></label>
                        <div class="item-name"><?php echo htmlspecialchars($bild[$item_key]); ?></div>
                    </div>
                <?php endif; endfor; ?>
            </div>
        </div>

        <div class="section">
            <h2>Runy</h2>
            <div class="items-grid">
                <?php if ($bild['keystone_rune']): ?>
                    <div class="item-card">
                        <label>Keystone</label>
                        <div class="item-name"><?php echo htmlspecialchars($bild['keystone_rune']); ?></div>
                    </div>
                <?php endif; ?>
                <?php for ($i = 1; $i <= 5; $i++): 
                    $rune_key = "rune$i";
                    if ($bild[$rune_key]):
                ?>
                    <div class="item-card">
                        <label>Runa <?php echo $i; ?></label>
                        <div class="item-name"><?php echo htmlspecialchars($bild[$rune_key]); ?></div>
                    </div>
                <?php endif; endfor; ?>
            </div>
        </div>

        <div class="section">
            <h2>Czary przywoływacza</h2>
            <div class="items-grid">
                <?php if ($bild['summoner1']): ?>
                    <div class="item-card">
                        <label>Czar 1</label>
                        <div class="item-name"><?php echo htmlspecialchars($bild['summoner1']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($bild['summoner2']): ?>
                    <div class="item-card">
                        <label>Czar 2</label>
                        <div class="item-name"><?php echo htmlspecialchars($bild['summoner2']); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <h2>Komentarze i recenzje</h2>

            <div class="stats">
                <div class="stat">
                    <span class="stat-number"><?php echo $bild['liczba_polubien']; ?></span>
                    <span class="stat-label">polubień</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?php echo count($komentarze); ?></span>
                    <span class="stat-label">komentarzy</span>
                </div>
            </div>

            <?php if (czy_zalogowany()): ?>
                <div class="comment-form">
                    <h3>Dodaj recenzję</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_comment">
                        
                        <div class="form-group">
                            <label for="ocena">Ocena</label>
                            <select id="ocena" name="ocena" required>
                                <option value="5">⭐⭐⭐⭐⭐ Świetnie</option>
                                <option value="4">⭐⭐⭐⭐ Dobrze</option>
                                <option value="3">⭐⭐⭐ Średnio</option>
                                <option value="2">⭐⭐ Słabo</option>
                                <option value="1">⭐ Bardzo źle</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tresc_komentarza">Twoja recenzja</label>
                            <textarea id="tresc_komentarza" name="tresc_komentarza" placeholder="Podziel się swoją opinią na temat tego białku..." required></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Wyślij recenzję</button>
                    </form>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #999;">
                    <a href="login-register.php" style="color: #0084ff;">Zaloguj się</a> aby dodać recenzję
                </p>
            <?php endif; ?>

            <h3 style="margin-top: 30px;">Recenzje użytkowników</h3>
            <?php if (count($komentarze) > 0): ?>
                <?php foreach ($komentarze as $komentarz): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span class="comment-author"><?php echo htmlspecialchars($komentarz['nazwa_uzytkownika']); ?></span>
                            <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($komentarz['data_komentarza'])); ?></span>
                        </div>
                        <div class="comment-rating">
                            <?php echo str_repeat('⭐', $komentarz['ocena']); ?> (<?php echo $komentarz['ocena']; ?>/5)
                        </div>
                        <div class="comment-text">
                            <?php echo nl2br(htmlspecialchars($komentarz['tresc_komentarza'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-comments">
                    <p>Brak komentarzy. Bądź pierwszy, aby dodać recenzję!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
