<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funkcje.php';

if (!isset($_GET['id'])) {
    header('Location: dyskusje.php');
    exit;
}
$thread_id = (int)$_GET['id'];

$stmt = $pdo->prepare('
    SELECT d.*, u.nazwa_uzytkownika, u.avatar_url, u.id AS autor_id
    FROM dyskusje d
    JOIN uzytkownicy u ON d.uzytkownik_id = u.id
    WHERE d.id = ?
');
$stmt->execute([$thread_id]);
$watek = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$watek) {
    echo 'Wątek nie istnieje.';
    exit;
}

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    if (!czy_zalogowany()) {
        $msg = 'Musisz być zalogowany.';
    } else {
        $tresc = trim($_POST['tresc'] ?? '');
        if (strlen($tresc) < 2) {
            $msg = 'Wiadomość jest za krótka.';
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO dyskusja_odpowiedzi (dyskusja_id, uzytkownik_id, tresc, data_dodania)
                VALUES (?, ?, ?, NOW())
            ');
            $stmt->execute([$thread_id, $_SESSION['user_id'], $tresc]);
            $msg = 'Odpowiedź dodana.';
        }
    }
}

$stmt = $pdo->prepare('
    SELECT o.*, u.nazwa_uzytkownika, u.avatar_url
    FROM dyskusja_odpowiedzi o
    JOIN uzytkownicy u ON o.uzytkownik_id = u.id
    WHERE o.dyskusja_id = ?
    ORDER BY o.data_dodania ASC
');
$stmt->execute([$thread_id]);
$odpowiedzi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($watek['tytul']) ?> – Dyskusje</title>
  <link rel="stylesheet" href="css/dyskusja.css">
</head>
<body>
  <div class="landing">
  
    <header class="landing-nav">
      <div class="nav-left">
        <a href="index.php" class="logo-badge">Guider</a>
        <nav class="nav-links">
          <a href="ogrze.html">O grze</a>
          <a href="top.html">Top</a>
          <a href="jungle.html">Jungle</a>
          <a href="mid.html">Mid</a>
          <a href="bot.html">Adc</a>
          <a href="support.html">Support</a>
          <a href="dyskusje.php" class="nav-link--active">Dyskusje</a>
        </nav>
      </div>
      <div class="nav-right">
        <a href="register.php" class="nav-text-link">Zarejestruj się</a>
        <a href="login.php" class="nav-text-link">Zaloguj się</a>
        <a href="profile.php" class="btn btn-primary btn--sm">Moje konto</a>
      </div>
    </header>

    <main class="thread-main">
      <header class="thread-page-header">
        <a href="dyskusje.php" class="back-link">&larr; Wróć do listy dyskusji</a>
        <h1 class="thread-title-main"><?= htmlspecialchars($watek['tytul']) ?></h1>
      </header>

    
      <section class="thread-start">
        <div class="thread-start-avatar">
          <a href="view_profile.php?id=<?= (int)$watek['autor_id'] ?>">
            <img
              src="<?= htmlspecialchars($watek['avatar_url'] ?: 'avatars/Default-Icon.jpg') ?>"
              alt="Avatar autora"
              class="avatar"
            >
          </a>
        </div>
        <div class="thread-start-body">
          <div class="thread-start-meta">
            <a href="view_profile.php?id=<?= (int)$watek['autor_id'] ?>" class="chat-user-link">
              <span class="author"><?= htmlspecialchars($watek['nazwa_uzytkownika']) ?></span>
            </a>
            <span class="date"><?= htmlspecialchars($watek['data_dodania']) ?></span>
          </div>
          <div class="thread-start-bubble">
            <p><?= nl2br(htmlspecialchars($watek['tresc'])) ?></p>
          </div>
        </div>
      </section>

      <?php if ($msg): ?>
        <div class="thread-message"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      
      <section class="thread-chat">
        <?php foreach ($odpowiedzi as $o): ?>
          <?php $is_me = isset($_SESSION['user_id']) && $o['uzytkownik_id'] == $_SESSION['user_id']; ?>
          <div class="chat-row <?= $is_me ? 'me' : 'other' ?>">
            <div class="chat-avatar">
              <a href="view_profile.php?id=<?= (int)$o['uzytkownik_id'] ?>">
                <img
                  src="<?= htmlspecialchars($o['avatar_url'] ?: 'avatars/Default-Icon.jpg') ?>"
                  alt="Avatar"
                  class="avatar-small"
                >
              </a>
            </div>
            <div class="chat-bubble-wrap">
              <div class="chat-meta">
                <a href="view_profile.php?id=<?= (int)$o['uzytkownik_id'] ?>" class="chat-user-link">
                  <span class="chat-author"><?= htmlspecialchars($o['nazwa_uzytkownika']) ?></span>
                </a>
                <span class="chat-date"><?= htmlspecialchars($o['data_dodania']) ?></span>
              </div>
              <div class="chat-bubble">
                <p><?= nl2br(htmlspecialchars($o['tresc'])) ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($odpowiedzi)): ?>
          <p class="chat-empty">Brak odpowiedzi. Bądź pierwszym, który coś napisze!</p>
        <?php endif; ?>
      </section>

  
      <?php if (czy_zalogowany()): ?>
        <form method="POST" class="chat-form">
          <textarea name="tresc" rows="3" placeholder="Napisz odpowiedź..." required></textarea>
          <button type="submit" name="reply" class="btn btn-primary">Wyślij</button>
        </form>
      <?php else: ?>
        <p class="thread-info">Zaloguj się, aby pisać w dyskusji.</p>
      <?php endif; ?>

      <footer class="landing-footer">
        <p>Serhii Brachan | Projekt dyplomowy</p>
        <a href="https://www.leagueoflegends.com" target="_blank">Oficjalna strona Riot Games</a>
      </footer>
    </main>
  </div>
</body>
</html>
