<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funkcje.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    header('Location: dyskusje.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nazwa_uzytkownika, avatar_url, data_rejestracji, biografia, fav_role 
                       FROM uzytkownicy WHERE id = ?');
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    header('Location: dyskusje.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Profil gracza <?= htmlspecialchars($profile['nazwa_uzytkownika']) ?> – League Guide</title>
  <link rel="stylesheet" href="css/profile.css">
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
          <a href="dyskusje.php">Dyskusje</a>
        </nav>
      </div>
      <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="profile.php" class="btn btn-ghost btn--sm">Moje konto</a>
        <?php else: ?>
          <a href="login.php" class="nav-text-link">Zaloguj się</a>
        <?php endif; ?>
      </div>
    </header>

    <main class="profile-main">
      <section class="profile-hero">
        <div class="profile-hero-left">
          <img
            src="<?= htmlspecialchars($profile['avatar_url'] ?: 'avatars/Default-Icon.jpg') ?>"
            alt="Avatar"
            class="profile-avatar-main"
          >
          <div>
            <h1 class="profile-name"><?= htmlspecialchars($profile['nazwa_uzytkownika']) ?></h1>
            <p class="profile-join">
              Członek od
              <span><?= date('d.m.Y', strtotime($profile['data_rejestracji'])) ?></span>
            </p>
            <?php if (!empty($profile['fav_role'])): ?>
              <p class="profile-role">
                Ulubiona rola: <strong><?= htmlspecialchars($profile['fav_role']) ?></strong>
              </p>
            <?php endif; ?>
          </div>
        </div>
        <div class="profile-hero-right">
          <a href="dyskusje.php" class="btn btn-primary">Wróć do dyskusji</a>
        </div>
      </section>

      <section class="profile-grid">
        <section class="avatar-section">
          <h2>O graczu</h2>
          <p class="profile-text">
            <?= nl2br(htmlspecialchars($profile['biografia'] ?: 'Ten gracz nie dodał jeszcze opisu.')) ?>
          </p>
        </section>

        <section class="profile-settings">
          <h2>Aktywność na forum</h2>
          <?php
      $stmt = $pdo->prepare('SELECT COUNT(*) FROM dyskusje WHERE uzytkownik_id = ?');
$stmt->execute([$user_id]);
$threads = (int)$stmt->fetchColumn();


          $stmt = $pdo->prepare('SELECT COUNT(*) FROM dyskusja_odpowiedzi WHERE uzytkownik_id = ?');
          $stmt->execute([$user_id]);
          $replies = (int)$stmt->fetchColumn();
          ?>
          <p class="profile-text">Utworzone wątki: <strong><?= $threads ?></strong></p>
          <p class="profile-text">Odpowiedzi w dyskusjach: <strong><?= $replies ?></strong></p>
        </section>
      </section>

      <footer class="landing-footer">
        <p>Serhii Brachan | Projekt dyplomowy</p>
        <a href="https://www.leagueoflegends.com" target="_blank">Oficjalna strona Riot Games</a>
      </footer>
    </main>
  </div>
</body>
</html>
