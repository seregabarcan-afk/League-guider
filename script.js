document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    const message = document.getElementById("message");

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();

    
        const saved = localStorage.getItem("lol_accounts");
        if (!saved) {
            message.textContent = "Brak zarejestrowanych kont. Załóż konto.";
            return;
        }

        /** @type {{username:string,email:string,password:string}[]} */
        let accounts;
        try {
            accounts = JSON.parse(saved);
        } catch {
            accounts = [];
        }


        const acc = accounts.find(a => a.username === username);

        if (!acc || acc.password !== password) {
            message.textContent = "Nieprawidłowa nazwa użytkownika lub hasło.";
            return;
        }

     
        localStorage.setItem("lol_logged_in", "true");
        localStorage.setItem("lol_username", acc.username);
        localStorage.setItem("lol_email", acc.email);

        window.location.href = "profile.html"; 
    });
});
