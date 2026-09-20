document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    const message = document.getElementById("message");

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();

        const saved = localStorage.getItem("lol_account");
        if (!saved) {
            message.textContent = "Brak zarejestrowanego konta. Załóż konto.";
            return;
        }

        const acc = JSON.parse(saved);

        if (username === acc.username && password === acc.password) {
            localStorage.setItem("lol_logged_in", "true");
            localStorage.setItem("lol_username", acc.username);
            localStorage.setItem("lol_email", acc.email);
            window.location.href = "profile.html";
        } else {
            message.textContent = "Nieprawidłowa nazwa użytkownika lub hasło.";
        }
    });
});
