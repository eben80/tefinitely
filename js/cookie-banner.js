(function() {
    const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
        'IS', 'LI', 'NO', 'CH', 'GB' // EEA, Switzerland, and UK
    ];

    async function isEUUser() {
        const cached = localStorage.getItem('isEUUser');
        if (cached !== null) {
            return cached === 'true';
        }

        try {
            const response = await fetch('https://ipapi.co/json/');
            const data = await response.json();
            const isEU = EU_COUNTRIES.includes(data.country_code) || data.in_eu;
            localStorage.setItem('isEUUser', isEU);
            return isEU;
        } catch (error) {
            console.error('Error detecting location:', error);
            // Default to false or true? Usually safer to show if unsure,
            // but "only shows to EU users" suggests we should be sure.
            // Let's default to false to avoid annoying non-EU users if API fails.
            return false;
        }
    }

    function showBanner() {
        if (localStorage.getItem('cookieBannerDismissed')) {
            return;
        }

        const banner = document.createElement('div');
        banner.id = 'cookie-banner';
        banner.innerHTML = `
            <div class="cookie-banner-content">
                <p>We use cookies to improve your experience. By continuing to use our site, you agree to our use of cookies.</p>
                <div class="cookie-banner-actions">
                    <button id="accept-cookies">Accept</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);

        document.getElementById('accept-cookies').addEventListener('click', () => {
            banner.remove();
            localStorage.setItem('cookieBannerDismissed', 'true');
        });
    }

    async function init() {
        if (await isEUUser()) {
            showBanner();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
