document.addEventListener('DOMContentLoaded', () => {
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const navContent = document.getElementById('nav-content');

    if (hamburgerMenu && navContent) {
        hamburgerMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = navContent.classList.toggle('is-open');
            hamburgerMenu.setAttribute('aria-expanded', isOpen);
        });
    }

    // Handle Dropdowns
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const btn = dropdown.querySelector('.dropbtn');
        const content = dropdown.querySelector('.dropdown-content');

        if (btn && content) {
            btn.addEventListener('click', (e) => {
                // Mobile behavior: toggle on click
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();

                    const wasOpen = content.classList.contains('is-open');

                    // Close all other main dropdowns
                    document.querySelectorAll('.dropdown-content.is-open').forEach(el => {
                        if (el !== content) el.classList.remove('is-open');
                    });
                    // Close all sub-dropdowns when switching main dropdowns
                    document.querySelectorAll('.sub-dropdown.is-open, .sub-dropdown-content.is-open').forEach(el => {
                        el.classList.remove('is-open');
                    });

                    if (!wasOpen) {
                        content.classList.add('is-open');
                    }
                }
            });
        }
    });

    // Handle Sub-Dropdowns
    const subDropdowns = document.querySelectorAll('.sub-dropdown');
    subDropdowns.forEach(sub => {
        const btn = sub.querySelector('.sub-dropbtn');
        const content = sub.querySelector('.sub-dropdown-content');

        if (btn && content) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const wasOpen = content.classList.contains('is-open');

                // Close other sub-dropdowns in the same parent
                const parentContent = sub.closest('.dropdown-content');
                if (parentContent) {
                    parentContent.querySelectorAll('.sub-dropdown').forEach(el => {
                        if (el !== sub) {
                            el.classList.remove('is-open');
                            const c = el.querySelector('.sub-dropdown-content');
                            if (c) c.classList.remove('is-open');
                        }
                    });
                }

                if (!wasOpen) {
                    sub.classList.add('is-open');
                    content.classList.add('is-open');
                } else {
                    sub.classList.remove('is-open');
                    content.classList.remove('is-open');
                }
            });
        }
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.main-nav')) {
            document.querySelectorAll('.is-open').forEach(el => el.classList.remove('is-open'));
            if (hamburgerMenu) hamburgerMenu.setAttribute('aria-expanded', 'false');
        }
    });
});
