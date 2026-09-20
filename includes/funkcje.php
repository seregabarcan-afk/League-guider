<?php
/**
 * Rejestracja nowego użytkownika
 * @param string $nazwa_uzytkownika
 * @param string $email
 * @param string $haslo
 * @param PDO $pdo
 * @return array ['success' => true/false, 'message' => string]
 */
function zarejestruj_uzytkownika($nazwa_uzytkownika, $email, $haslo, $pdo) {

    if (strlen($nazwa_uzytkownika) < 3) {
        return ['success' => false, 'message' => 'Nazwa użytkownika musi mieć co najmniej 3 znaki'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Nieprawidłowy adres email'];
    }
    if (strlen($haslo) < 6) {
        return ['success' => false, 'message' => 'Hasło musi mieć co najmniej 6 znaków'];
    }

    $hashed_password = password_hash($haslo, PASSWORD_BCRYPT);


    try {
       
        $stmt = $pdo->prepare('SELECT id FROM uzytkownicy WHERE nazwa_uzytkownika = ? OR email = ?');
        $stmt->execute([$nazwa_uzytkownika, $email]);
       
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Użytkownik lub email już istnieje'];
        }

        $stmt = $pdo->prepare('INSERT INTO uzytkownicy (nazwa_uzytkownika, email, haslo) VALUES (?, ?, ?)');
        $stmt->execute([$nazwa_uzytkownika, $email, $hashed_password]);


        return ['success' => true, 'message' => 'Rejestracja pomyślna! Możesz się zalogować.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd bazy danych: ' . $e->getMessage()];
    }
}


/**
 * Logowanie użytkownika
 * @param string $nazwa_uzytkownika
 * @param string $haslo
 * @param PDO $pdo
 * @return array ['success' => true/false, 'message' => string, 'user_id' => int|null]
 */
function zaloguj_uzytkownika($nazwa_uzytkownika, $haslo, $pdo) {
    try {
        $stmt = $pdo->prepare('SELECT id, haslo FROM uzytkownicy WHERE nazwa_uzytkownika = ?');
        $stmt->execute([$nazwa_uzytkownika]);
       
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Użytkownik nie znaleziony'];
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($haslo, $user['haslo'])) {
            return ['success' => false, 'message' => 'Błędne hasło'];
        }

        $stmt_update = $pdo->prepare('UPDATE uzytkownicy SET data_ostatniego_logowania = NOW() WHERE id = ?');
        $stmt_update->execute([$user['id']]);


        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $nazwa_uzytkownika;


        return ['success' => true, 'message' => 'Zalogowano pomyślnie', 'user_id' => $user['id']];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd bazy danych'];
    }
}


function wyloguj_uzytkownika() {
    session_unset();
    session_destroy();
}


function czy_zalogowany() {
    return isset($_SESSION['user_id']);
}


function pobierz_aktualnego_uzytkownika($pdo) {
    if (!czy_zalogowany()) {
        return null;
    }


    try {
        $stmt = $pdo->prepare('SELECT id, nazwa_uzytkownika, email, biografia, avatar_url, data_rejestracji FROM uzytkownicy WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}


function dodaj_bild($dane_bildu, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }


    try {
        $stmt = $pdo->prepare('
            INSERT INTO bildy (
                uzytkownik_id, nazwa_bildu, postac, rola, opis,
                boot_item, item1, item2, item3, item4, item5,
                keystone_rune, rune1, rune2, rune3, rune4, rune5,
                summoner1, summoner2
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?
            )
        ');


        $stmt->execute([
            $_SESSION['user_id'],
            $dane_bildu['nazwa_bildu'],
            $dane_bildu['postac'],
            $dane_bildu['rola'],
            $dane_bildu['opis'] ?? null,
            $dane_bildu['boot_item'] ?? null,
            $dane_bildu['item1'] ?? null,
            $dane_bildu['item2'] ?? null,
            $dane_bildu['item3'] ?? null,
            $dane_bildu['item4'] ?? null,
            $dane_bildu['item5'] ?? null,
            $dane_bildu['keystone_rune'] ?? null,
            $dane_bildu['rune1'] ?? null,
            $dane_bildu['rune2'] ?? null,
            $dane_bildu['rune3'] ?? null,
            $dane_bildu['rune4'] ?? null,
            $dane_bildu['rune5'] ?? null,
            $dane_bildu['summoner1'] ?? null,
            $dane_bildu['summoner2'] ?? null
        ]);


        $bild_id = $pdo->lastInsertId();
        return ['success' => true, 'message' => 'Białek dodany pomyślnie!', 'bild_id' => $bild_id];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd: ' . $e->getMessage()];
    }
}


function pobierz_bildy_uzytkownika($uzytkownik_id, $pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT b.*, u.nazwa_uzytkownika
            FROM bildy b
            JOIN uzytkownicy u ON b.uzytkownik_id = u.id
            WHERE b.uzytkownik_id = ?
            ORDER BY b.data_dodania DESC
        ');
        $stmt->execute([$uzytkownik_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}



function pobierz_bild_po_id($bild_id, $pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT b.*, u.nazwa_uzytkownika, u.avatar_url
            FROM bildy b
            JOIN uzytkownicy u ON b.uzytkownik_id = u.id
            WHERE b.id = ?
        ');
        $stmt->execute([$bild_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}



function edytuj_bild($bild_id, $dane_bildu, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }



    $bild = pobierz_bild_po_id($bild_id, $pdo);
    if (!$bild || $bild['uzytkownik_id'] != $_SESSION['user_id']) {
        return ['success' => false, 'message' => 'Nie masz dostępu do tego białku'];
    }


    try {
        $stmt = $pdo->prepare('
            UPDATE bildy SET
                nazwa_bildu = ?, opis = ?,
                boot_item = ?, item1 = ?, item2 = ?, item3 = ?, item4 = ?, item5 = ?,
                keystone_rune = ?, rune1 = ?, rune2 = ?, rune3 = ?, rune4 = ?, rune5 = ?,
                summoner1 = ?, summoner2 = ?
            WHERE id = ?
        ');


        $stmt->execute([
            $dane_bildu['nazwa_bildu'],
            $dane_bildu['opis'] ?? null,
            $dane_bildu['boot_item'] ?? null,
            $dane_bildu['item1'] ?? null,
            $dane_bildu['item2'] ?? null,
            $dane_bildu['item3'] ?? null,
            $dane_bildu['item4'] ?? null,
            $dane_bildu['item5'] ?? null,
            $dane_bildu['keystone_rune'] ?? null,
            $dane_bildu['rune1'] ?? null,
            $dane_bildu['rune2'] ?? null,
            $dane_bildu['rune3'] ?? null,
            $dane_bildu['rune4'] ?? null,
            $dane_bildu['rune5'] ?? null,
            $dane_bildu['summoner1'] ?? null,
            $dane_bildu['summoner2'] ?? null,
            $bild_id
        ]);


        return ['success' => true, 'message' => 'Białek zaktualizowany pomyślnie!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd: ' . $e->getMessage()];
    }
}



function usun_bild($bild_id, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }


    $bild = pobierz_bild_po_id($bild_id, $pdo);
    if (!$bild || $bild['uzytkownik_id'] != $_SESSION['user_id']) {
        return ['success' => false, 'message' => 'Nie masz dostępu do tego białku'];
    }


    try {
        $stmt = $pdo->prepare('DELETE FROM bildy WHERE id = ?');
        $stmt->execute([$bild_id]);
        return ['success' => true, 'message' => 'Białek usunięty pomyślnie!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd: ' . $e->getMessage()];
    }
}






function dodaj_komentarz($bild_id, $tresc, $ocena, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }


    if (empty($tresc) || strlen($tresc) < 5) {
        return ['success' => false, 'message' => 'Komentarz musi mieć co najmniej 5 znaków'];
    }


    if ($ocena < 1 || $ocena > 5) {
        return ['success' => false, 'message' => 'Ocena musi być między 1 a 5'];
    }


    try {
        $stmt = $pdo->prepare('
            INSERT INTO komentarze (bild_id, uzytkownik_id, tresc_komentarza, ocena)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$bild_id, $_SESSION['user_id'], $tresc, $ocena]);


      
        $stmt_update = $pdo->prepare('UPDATE bildy SET liczba_komentarzy = liczba_komentarzy + 1 WHERE id = ?');
        $stmt_update->execute([$bild_id]);


        return ['success' => true, 'message' => 'Komentarz dodany pomyślnie!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd: ' . $e->getMessage()];
    }
}


function pobierz_komentarze($bild_id, $pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT k.*, u.nazwa_uzytkownika, u.avatar_url
            FROM komentarze k
            JOIN uzytkownicy u ON k.uzytkownik_id = u.id
            WHERE k.bild_id = ?
            ORDER BY k.data_komentarza DESC
        ');
        $stmt->execute([$bild_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function polub_bild($bild_id, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }


    try {
        $stmt = $pdo->prepare('
            INSERT INTO polubienia_bildu (uzytkownik_id, bild_id)
            VALUES (?, ?)
        ');
        $stmt->execute([$_SESSION['user_id'], $bild_id]);

        $stmt_update = $pdo->prepare('UPDATE bildy SET liczba_polubien = liczba_polubien + 1 WHERE id = ?');
        $stmt_update->execute([$bild_id]);


        return ['success' => true, 'message' => 'Białek polubiony!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Już polubiłeś ten białek'];
    }
}

function usun_polubienie($bild_id, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }


    try {
        $stmt = $pdo->prepare('DELETE FROM polubienia_bildu WHERE uzytkownik_id = ? AND bild_id = ?');
        $stmt->execute([$_SESSION['user_id'], $bild_id]);

        $stmt_update = $pdo->prepare('UPDATE bildy SET liczba_polubien = liczba_polubien - 1 WHERE id = ?');
        $stmt_update->execute([$bild_id]);


        return ['success' => true, 'message' => 'Polubienie usunięte'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd: ' . $e->getMessage()];
    }
}

function zapisz_bild($bild_id, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }


    try {
        $stmt = $pdo->prepare('INSERT INTO zapisane_bildy (uzytkownik_id, bild_id) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $bild_id]);
        return ['success' => true, 'message' => 'Białek zapisany!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Już masz ten białek w zbiorze'];
    }
}



function usun_zapisany_bild($bild_id, $pdo) {
    if (!czy_zalogowany()) {
        return ['success' => false, 'message' => 'Musisz być zalogowany'];
    }


    try {
        $stmt = $pdo->prepare('DELETE FROM zapisane_bildy WHERE uzytkownik_id = ? AND bild_id = ?');
        $stmt->execute([$_SESSION['user_id'], $bild_id]);
        return ['success' => true, 'message' => 'Białek usunięty z kolekcji'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Błąd: ' . $e->getMessage()];
    }
}



function pobierz_zapisane_bildy($pdo) {
    if (!czy_zalogowany()) {
        return [];
    }


    try {
        $stmt = $pdo->prepare('
            SELECT b.*, u.nazwa_uzytkownika
            FROM zapisane_bildy zb
            JOIN bildy b ON zb.bild_id = b.id
            JOIN uzytkownicy u ON b.uzytkownik_id = u.id
            WHERE zb.uzytkownik_id = ?
            ORDER BY zb.data_zapisania DESC
        ');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}


?>