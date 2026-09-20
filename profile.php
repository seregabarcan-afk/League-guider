<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funkcje.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM uzytkownicy WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$avatar_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_avatar'])) {
    $new_avatar = $_POST['avatar'] ?? '';

    $allowed = [
        'avatars/teemo.jpg',
        'avatars/Jinx.png',
        'avatars/yasuo.jpg',
        'avatars/lux.png',
        'avatars/Default-Icon.jpg'
    ];

    if (in_array($new_avatar, $allowed, true)) {
        $stmt = $pdo->prepare('UPDATE uzytkownicy SET avatar_url = ? WHERE id = ?');
        $stmt->execute([$new_avatar, $_SESSION['user_id']]);
        $user['avatar_url'] = $new_avatar;
        $avatar_msg = 'Avatar zaktualizowany.';
    } else {
        $avatar_msg = 'Nieprawidłowy avatar.';
    }
}

$profile_msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $bio  = trim($_POST['biografia'] ?? '');
    $role = trim($_POST['fav_role'] ?? '');

    $stmt = $pdo->prepare('UPDATE uzytkownicy SET biografia = ?, fav_role = ? WHERE id = ?');
    $stmt->execute([$bio, $role, $_SESSION['user_id']]);

    $user['biografia'] = $bio;
    $user['fav_role']  = $role;
    $profile_msg = 'Profil zaktualizowany.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    $_SESSION = [];
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Mój profil – League Guide</title>
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
        <form method="POST">
          <button type="submit" name="logout" class="btn btn-ghost btn--sm">Wyloguj się</button>
        </form>
      </div>
    </header>

    <main class="profile-main">
     
      <section class="profile-hero">
        <div class="profile-hero-left">
          <img
            src="<?= htmlspecialchars($user['avatar_url'] ?: 'avatars/Default-Icon.jpg') ?>"
            alt="Avatar"
            class="profile-avatar-main"
          >
          <div>
            <h1 class="profile-name"><?= htmlspecialchars($user['nazwa_uzytkownika']) ?></h1>
            <p class="profile-join">
              Członek od
              <span><?= date('d.m.Y', strtotime($user['data_rejestracji'])) ?></span>
            </p>
            <?php if (!empty($user['fav_role'])): ?>
              <p class="profile-role">
                Ulubiona rola: <strong><?= htmlspecialchars($user['fav_role']) ?></strong>
              </p>
            <?php endif; ?>
          </div>
        </div>
        <div class="profile-hero-right">
          <a href="index.php" class="btn btn-primary">Strona główna</a>
        </div>
      </section>

      <section class="profile-grid">
     
        <section class="avatar-section">
          <h2>Mój avatar</h2>

          <?php if ($avatar_msg): ?>
            <p class="avatar-msg"><?= htmlspecialchars($avatar_msg) ?></p>
          <?php endif; ?>

          <form method="POST" class="avatar-form">
            <div class="avatar-grid">
              <label class="avatar-option">
                <input type="radio" name="avatar" value="avatars/teemo.jpg"
                  <?= ($user['avatar_url'] ?? '') === 'avatars/teemo.jpg' ? 'checked' : '' ?>>
                <img src="avatars/teemo.jpg" alt="Teemo">
              </label>
              <label class="avatar-option">
                <input type="radio" name="avatar" value="avatars/Jinx.png"
                  <?= ($user['avatar_url'] ?? '') === 'avatars/Jinx.png' ? 'checked' : '' ?>>
                <img src="avatars/Jinx.png" alt="Jinx">
              </label>
              <label class="avatar-option">
                <input type="radio" name="avatar" value="avatars/yasuo.jpg"
                  <?= ($user['avatar_url'] ?? '') === 'avatars/yasuo.jpg' ? 'checked' : '' ?>>
                <img src="avatars/yasuo.jpg" alt="Yasuo">
              </label>
              <label class="avatar-option">
                <input type="radio" name="avatar" value="avatars/lux.png"
                  <?= ($user['avatar_url'] ?? '') === 'avatars/lux.png' ? 'checked' : '' ?>>
                <img src="avatars/lux.png" alt="Lux">
              </label>
              <label class="avatar-option">
                <input type="radio" name="avatar" value="avatars/Default-Icon.jpg"
                  <?= ($user['avatar_url'] ?? '') === 'avatars/Default-Icon.jpg' ? 'checked' : '' ?>>
                <img src="avatars/Default-Icon.jpg" alt="Domyślny">
              </label>
            </div>

            <button type="submit" name="change_avatar" class="btn btn-primary btn-save-avatar">
              Zapisz avatar
            </button>
          </form>
        </section>

        
        <section class="profile-settings">
          <h2>Ustawienia profilu</h2>

          <?php if ($profile_msg): ?>
            <p class="profile-msg"><?= htmlspecialchars($profile_msg) ?></p>
          <?php endif; ?>

          <form method="POST" class="profile-form">
            <label class="profile-label">
              Krótki opis o Tobie
              <textarea name="biografia" class="profile-textarea" rows="4"
                placeholder="Napisz kilka słów o sobie..."><?= htmlspecialchars($user['biografia'] ?? '') ?></textarea>
            </label>

            <label class="profile-label">
              Ulubiona rola w League of Legends
              <select name="fav_role" class="profile-select">
                <?php
                  $roles = ['Top','Jungle','Mid','Adc','Support'];
                  $current = $user['fav_role'] ?? '';
                  foreach ($roles as $r) {
                      $sel = ($r === $current) ? 'selected' : '';
                      echo "<option value=\"$r\" $sel>$r</option>";
                  }
                ?>
              </select>
            </label>

            <button type="submit" name="save_profile" class="btn btn-primary">
              Zapisz profil
            </button>
          </form>
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
