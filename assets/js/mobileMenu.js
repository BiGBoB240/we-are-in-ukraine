// Мобільне меню як модалка

document.addEventListener('DOMContentLoaded', function() {
    // Створюємо модалку для мобільного меню
    const modal = document.createElement('div');
    modal.className = 'mobile-menu-modal';
    modal.innerHTML = `
        <div class="mobile-menu-modal__backdrop"></div>
        <div class="mobile-menu-modal__content">
            <button class="mobile-menu-modal__close" aria-label="Закрити меню">&times;</button>
            <nav class="mobile-menu-nav">
                ${document.querySelector('.nav-links').innerHTML}
            </nav>
        </div>
    `;
    document.body.appendChild(modal);
    modal.style.display = 'none';

    const navToggle = document.querySelector('.nav-toggle');
    navToggle.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'flex';
    });

    // Закриття модалки
    modal.querySelector('.mobile-menu-modal__close').onclick = closeModal;
    modal.querySelector('.mobile-menu-modal__backdrop').onclick = closeModal;
    // Закриття по кліку на пункт
    modal.querySelectorAll('.mobile-menu-nav a').forEach(link => {
        link.addEventListener('click', closeModal);
    });
    function closeModal() {
        modal.style.display = 'none';
    }

    // Показувати тільки на малих екранах
    function handleResize() {
        if(window.innerWidth <= 700) {
            navToggle.style.display = '';
            document.querySelector('.nav-links').style.display = 'none';
        } else {
            navToggle.style.display = 'none';
            document.querySelector('.nav-links').style.display = '';
            modal.style.display = 'none';
        }
    }
    window.addEventListener('resize', handleResize);
    handleResize();
});
