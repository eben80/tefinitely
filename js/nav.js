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

    // Toggle for main dropdowns on mobile
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const btn = dropdown.querySelector('.dropbtn');
        const content = dropdown.querySelector('.dropdown-content');

        if (btn && content) {
            btn.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    content.classList.toggle('is-open');

                    // Close other dropdowns
                    dropdowns.forEach(other => {
                        if (other !== dropdown) {
                            const otherContent = other.querySelector('.dropdown-content');
                            if (otherContent) otherContent.classList.remove('is-open');
                        }
                    });
                }
            });
        }
    });

    // Toggle for sub-dropdowns (both mobile and potentially desktop click)
    const subDropdowns = document.querySelectorAll('.sub-dropdown');
    subDropdowns.forEach(sub => {
        const btn = sub.querySelector('.sub-dropbtn');
        const content = sub.querySelector('.sub-dropdown-content');

        if (btn && content) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                sub.classList.toggle('is-open');
                content.classList.toggle('is-open');

                // Close other sub-dropdowns in the same parent
                const parent = sub.parentElement;
                const siblings = parent.querySelectorAll('.sub-dropdown');
                siblings.forEach(sibling => {
                    if (sibling !== sub) {
                        sibling.classList.remove('is-open');
                        const siblingContent = sibling.querySelector('.sub-dropdown-content');
                        if (siblingContent) siblingContent.classList.remove('is-open');
                    }
                });
            });
        }
    });

    // Close menus when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.main-nav')) {
            const openContents = document.querySelectorAll('.dropdown-content.is-open, .sub-dropdown-content.is-open, .sub-dropdown.is-open');
            openContents.forEach(el => el.classList.remove('is-open'));
            if (navContent) navContent.classList.remove('is-open');
            if (hamburgerMenu) hamburgerMenu.setAttribute('aria-expanded', 'false');
        }
    });
});
