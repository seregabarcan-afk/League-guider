# League Guider 🎮

**League Guider** to informacyjna strona internetowa poświęcona grze **League of Legends**, stworzona z myślą o początkujących i mniej doświadczonych graczach.

Projekt przedstawia podstawowe informacje dotyczące gry, poszczególnych ról oraz strategii. Użytkownik może również korzystać z systemu kont i forum dyskusyjnego.

## 📌 Funkcjonalności

* 🏠 Strona główna z podstawowymi informacjami o grze
* ⚔️ Poradniki dla poszczególnych pozycji:

  * Top
  * Jungle
  * Mid
  * Bot
  * Support
* 👤 Rejestracja i logowanie użytkowników
* 🔐 Obsługa sesji użytkownika
* 👤 Profile użytkowników
* 💬 Forum dyskusyjne
* 📝 Tworzenie i przeglądanie dyskusji
* 🎯 Informacje dotyczące run, przedmiotów i umiejętności
* 📱 Responsywny interfejs
* 🗄️ Połączenie z bazą danych MySQL

## 🛠️ Technologie

Projekt został wykonany przy wykorzystaniu:

* **HTML5**
* **CSS3**
* **JavaScript**
* **PHP**
* **MySQL**
* **PDO**
* **XAMPP**
* **phpMyAdmin**
* **Visual Studio Code**
* **Git / GitHub**

## 📂 Struktura projektu

```text
League-guider/
│
├── avatars/          # Awatary użytkowników
├── css/              # Arkusze stylów CSS
├── img/              # Obrazy, ikony, runy i przedmioty
├── includes/         # Pliki konfiguracyjne i funkcje PHP
│
├── index.php         # Strona główna
├── login.php         # Logowanie
├── register.php      # Rejestracja
├── profile.php       # Profil użytkownika
├── dyskusje.php      # Lista dyskusji
├── dyskusja.php      # Widok pojedynczej dyskusji
│
├── *.html            # Strony informacyjne
├── *.js              # Skrypty JavaScript
│
└── README.md
```

## 🗄️ Baza danych

Projekt wykorzystuje bazę danych **MySQL**.

Do lokalnego uruchomienia projektu można wykorzystać środowisko **XAMPP**, które zapewnia serwer Apache oraz MySQL.

Domyślna nazwa bazy danych używana przez projekt:

```text
praca_dyplomowa
```

> Struktura bazy danych nie jest automatycznie tworzona przez aplikację. Przed uruchomieniem projektu należy przygotować odpowiednią bazę danych w phpMyAdmin.

## 🚀 Uruchomienie lokalne

### 1. Zainstaluj XAMPP

Uruchom:

* Apache
* MySQL

### 2. Skopiuj projekt

Umieść projekt w katalogu:

```text
C:\xampp\htdocs\
```

Przykładowo:

```text
C:\xampp\htdocs\League-guider
```

### 3. Utwórz bazę danych

Otwórz phpMyAdmin i utwórz bazę:

```text
praca_dyplomowa
```

### 4. Sprawdź konfigurację

Konfiguracja połączenia z bazą danych znajduje się w:

```text
includes/config.php
```

### 5. Otwórz projekt

W przeglądarce:

```text
http://localhost/League-guider/
```

## 🎨 Interfejs

Projekt wykorzystuje ciemną kolorystykę inspirowaną uniwersum League of Legends, z elementami związanymi z poszczególnymi rolami oraz bohaterami.

Głównym celem projektu było stworzenie przejrzystego interfejsu, który pozwala początkującemu graczowi szybko znaleźć potrzebne informacje.

## 🎓 Projekt dyplomowy

Projekt został przygotowany jako część pracy dyplomowej na kierunku **Informatyka**.

Głównym celem było stworzenie funkcjonalnej aplikacji internetowej łączącej warstwę informacyjną z systemem użytkowników, forum oraz bazą danych.

## 👨‍💻 Autor

**Serhii Barchan**

Projekt wykonany w ramach studiów na kierunku Informatyka.

---

⭐ Jeśli projekt okazał się pomocny, możesz zostawić gwiazdkę na GitHubie.
