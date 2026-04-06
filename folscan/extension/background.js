const API_URL = "https://tefinitely.com/folscan/backend/api";

chrome.runtime.onInstalled.addListener(() => {
    // Check license periodically or on startup
    chrome.storage.local.get(["licenseKey"], (data) => {
        if (data.licenseKey) {
            validateLicense(data.licenseKey);
        }
    });
});

chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.type === "VALIDATE_LICENSE") {
        validateLicense(request.key).then(sendResponse);
        return true;
    }
});

async function validateLicense(licenseKey) {
    if (licenseKey === "MOCK-PREMIUM-KEY") {
        await chrome.storage.local.set({ isPremium: true, isPro: false, licenseKey: licenseKey });
        return { success: true, isPro: false };
    }
    if (licenseKey === "MOCK-PRO-KEY") {
        await chrome.storage.local.set({ isPremium: true, isPro: true, licenseKey: licenseKey });
        return { success: true, isPro: true };
    }
    try {
        const response = await fetch(`${API_URL}/validate.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ license_key: licenseKey })
        });
        const data = await response.json();
        if (data.status === "active") {
            // Assume default active license is premium, but not pro unless specified
            const isPro = data.tier === "pro";
            await chrome.storage.local.set({ isPremium: true, isPro: isPro, licenseKey: licenseKey });
            return { success: true };
        } else {
            await chrome.storage.local.set({ isPremium: false });
            return { success: false, message: "Invalid or inactive license key." };
        }
    } catch (error) {
        return { success: false, message: "Error connecting to server." };
    }
}
