(function() {
    function getBrowserInfo() {
        const ua = navigator.userAgent;
        let browser = "Unknown";
        let os = "Unknown";

        // OS Detection
        if (ua.indexOf("Win") !== -1) {
            os = "Windows";
        } else if (ua.indexOf("iPhone") !== -1 || ua.indexOf("iPad") !== -1 || ua.indexOf("iPod") !== -1) {
            os = "iOS";
        } else if (ua.indexOf("Mac") !== -1) {
            os = "Mac";
        } else if (ua.indexOf("Android") !== -1) {
            os = "Android";
        } else if (ua.indexOf("Linux") !== -1) {
            os = "Linux";
        }

        // Browser Detection
        if (ua.indexOf("Chrome") !== -1 && ua.indexOf("Edg") === -1 && ua.indexOf("OPR") === -1) {
            browser = "Chrome";
        } else if (ua.indexOf("Safari") !== -1 && ua.indexOf("Chrome") === -1) {
            browser = "Safari";
        } else if (ua.indexOf("Edg") !== -1) {
            browser = "Edge";
        } else if (ua.indexOf("Firefox") !== -1) {
            browser = "Firefox";
        }

        return { os, browser };
    }

    function isRecommended(info) {
        if (info.os === "Windows" && info.browser === "Chrome") return true;
        if ((info.os === "Mac" || info.os === "iOS") && info.browser === "Safari") return true;
        return false;
    }

    function showSupportPopup() {
        if (localStorage.getItem('browserSupportPopupShown')) return;

        const info = getBrowserInfo();
        if (isRecommended(info)) return;

        const popup = document.createElement('div');
        popup.id = 'browser-support-popup';
        popup.innerHTML = `
            <div class="browser-support-popup-content">
                <h3>Recommended Browser & OS</h3>
                <p>For the best experience, especially with speech functionality, we recommend using one of the following combinations:</p>
                <ul>
                    <li><strong>Windows</strong> with <strong>Google Chrome</strong></li>
                    <li><strong>iOS or macOS</strong> with <strong>Safari</strong></li>
                </ul>
                <p>Support for other combinations might vary with regards to speech functionality.</p>
                <div style="text-align: right; margin-top: 1.5rem;">
                    <button id="close-support-popup">Got it</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);

        document.getElementById('close-support-popup').addEventListener('click', () => {
            popup.style.display = 'none';
            localStorage.setItem('browserSupportPopupShown', 'true');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showSupportPopup);
    } else {
        showSupportPopup();
    }
})();
