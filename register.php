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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $message = 'Wypełnij wszystkie pola!';
        $message_type = 'error';
    } elseif (strlen($username) < 3) {
        $message = 'Nazwa użytkownika minimum 3 znaki!';
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Hasło minimum 6 znaków!';
        $message_type = 'error';
    } elseif ($password !== $password_confirm) {
        $message = 'Hasła się nie zgadzają!';
        $message_type = 'error';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM uzytkownicy WHERE nazwa_uzytkownika = ?');
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $message = 'Użytkownik już istnieje!';
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM uzytkownicy WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $message = 'Email już zarejestrowany!';
                $message_type = 'error';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $insert = $pdo->prepare('
                    INSERT INTO uzytkownicy (nazwa_uzytkownika, email, haslo, data_rejestracji)
                    VALUES (?, ?, ?, NOW())
                ');
                try {
                    $insert->execute([$username, $email, $hashed_password]);
                    $message = 'Rejestracja pomyślna! Przechodzisz do logowania...';
                    $message_type = 'success';
                    header('refresh:2;url=login.php');
                } catch (PDOException $e) {
                    $message = 'Błąd: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rejestracja – League Guide</title>
  <link rel="stylesheet" href="css/rejestracja.css">
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
        <a href="login.php" class="nav-text-link">Zaloguj się</a>
      </div>
    </header>

    <main class="auth-main">
      <section class="auth-card">
        <h1 class="auth-title">Zarejestruj się</h1>
        <p class="auth-subtitle">
          Utwórz konto, by zapisywać buildy i bierze udział w dyskusjach.
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
            minlength="3"
            required
          >

          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="Podaj email"
            required
          >

          <label for="password">Hasło</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Podaj hasło (min. 6 znaków)"
            minlength="6"
            required
          >

          <label for="password_confirm">Powtórz hasło</label>
          <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            placeholder="Powtórz hasło"
            minlength="6"
            required
          >

          <button type="submit" class="btn btn-primary auth-submit">
            Zarejestruj się
          </button>
        </form>

        <p class="auth-switch">
          Masz już konto?
          <a href="login.php">Zaloguj się</a>
        </p>

        <a href="index.php" class="auth-back-home">← Wróć na stronę główną</a>
      </section>
    </main>
  </div>
</body>
</html>
