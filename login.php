<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/funkcje.php';

$message = '';
$message_type = '';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = 'Wypełnij wszystkie pola!';
        $message_type = 'error';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM uzytkownicy WHERE nazwa_uzytkownika = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $message = 'Użytkownik nie znaleziony!';
            $message_type = 'error';
        } elseif (!password_verify($password, $user['haslo'])) {
            $message = 'Nieprawidłowe hasło!';
            $message_type = 'error';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nazwa_uzytkownika'];
            $_SESSION['email'] = $user['email'];

            header('Location: profile.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Logowanie – League Guide</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <div class="landing auth-landing">

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
        <a href="register.php" class="nav-text-link">Zarejestruj się</a>
      </div>
    </header>

    <main class="auth-main">
      <section class="auth-card">
        <h1 class="auth-title">Zaloguj się</h1>
        <p class="auth-subtitle">
          Wejdź do swojego konta, zapisuj buildy i bierz udział w dyskusjach.
        </p>

        <?php if ($message): ?>
          <div class="auth-message <?= htmlspecialchars($message_type) ?>">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
          <label for="username">Nazwa użytkownika</label>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Podaj nazwę użytkownika"
            required
          >

          <label for="password">Hasło</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Podaj hasło"
            required
          >

          <button type="submit" class="btn btn-primary auth-submit">
            Zaloguj się
          </button>
        </form>

        <p class="auth-switch">
          Nie masz konta?
          <a href="register.php">Zarejestruj się</a>
        </p>

        <a href="index.php" class="auth-back-home">← Wróć na stronę główną</a>
      </section>
    </main>
  </div>
</body>
</html>
