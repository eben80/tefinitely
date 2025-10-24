document.addEventListener('DOMContentLoaded', () => {
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const navContent = document.getElementById('nav-content');

    if (hamburgerMenu && navContent) {
        hamburgerMenu.addEventListener('click', () => {
            navContent.classList.toggle('is-open');
            const isExpanded = navContent.classList.contains('is-open');
            hamburgerMenu.setAttribute('aria-expanded', isExpanded);
        });
    }
});
