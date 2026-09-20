document.addEventListener('DOMContentLoaded', function () {
    const menu = document.querySelector('.user-menu');
    const toggleBtn = document.querySelector('.user-menu-toggle');

    if (menu && toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();              
            menu.classList.toggle('open');     
        });

        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target)) {
                menu.classList.remove('open');
            }
        });
    }
});
