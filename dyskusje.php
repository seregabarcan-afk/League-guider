<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funkcje.php';

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_thread'])) {
    if (!czy_zalogowany()) {
        $msg = 'Musisz być zalogowany, aby pisać na forum.';
    } else {
        $tytul = trim($_POST['tytul'] ?? '');
        $tresc = trim($_POST['tresc'] ?? '');

        if (strlen($tytul) < 3 || strlen($tresc) < 5) {
            $msg = 'Tytuł min. 3 znaki, treść min. 5 znaków.';
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO dyskusje (uzytkownik_id, tytul, tresc, data_dodania)
                VALUES (?, ?, ?, NOW())
            ');
            $stmt->execute([$_SESSION['user_id'], $tytul, $tresc]);
            $msg = 'Wątek utworzony.';
        }
    }
}

$stmt = $pdo->query('
    SELECT d.id, d.tytul, d.data_dodania, u.nazwa_uzytkownika
    FROM dyskusje d
    JOIN uzytkownicy u ON d.uzytkownik_id = u.id
    ORDER BY d.data_dodania DESC
');
$watki = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Dyskusje – League Guide</title>
  <link rel="stylesheet" href="css/dyskusje.css">
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

    <main class="forum-main">
      <section class="forum-hero">
        <div class="forum-hero-text">
          <p class="forum-kicker">Społeczność League Guide</p>
          <h1 class="forum-title">Dyskusje i pytania od graczy.</h1>
          <p class="forum-subtitle">
            Podziel się swoimi buildami, zapytaj o matchup albo dorzuć własne tipy dla innych graczy.
          </p>
        </div>
      </section>

      <?php if ($msg): ?>
        <div class="forum-message">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <?php if (czy_zalogowany()): ?>
        <section class="new-thread">
          <h2>Nowy wątek</h2>
          <form method="POST">
            <input type="text" name="tytul" placeholder="Tytuł wątku" required>
            <textarea name="tresc" rows="4" placeholder="O czym chcesz porozmawiać?" required></textarea>
            <button type="submit" name="new_thread" class="btn btn-primary">Dodaj wątek</button>
          </form>
        </section>
      <?php else: ?>
        <p class="forum-info">Zaloguj się, aby zakładać nowe wątki i odpowiadać na istniejące.</p>
      <?php endif; ?>

      <section class="threads-section">
        <div class="threads-header">
          <h2>Ostatnie wątki</h2>
        </div>

        <div class="threads-list">
          <?php if (empty($watki)): ?>
            <p class="threads-empty">Brak dyskusji. Zacznij pierwszy wątek!</p>
          <?php else: ?>
            <?php foreach ($watki as $w): ?>
              <a class="thread-card" href="dyskusja.php?id=<?= (int)$w['id'] ?>">
                <h3 class="thread-title"><?= htmlspecialchars($w['tytul']) ?></h3>
                <div class="thread-meta">
                  <span>Autor: <?= htmlspecialchars($w['nazwa_uzytkownika']) ?></span>
                  <span><?= htmlspecialchars($w['data_dodania']) ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <footer class="landing-footer">
        <p>Serhii Brachan | Projekt dyplomowy</p>
        <a href="https://www.leagueoflegends.com" target="_blank">Oficjalna strona Riot Games</a>
      </footer>
    </main>
  </div>
</body>
</html>
