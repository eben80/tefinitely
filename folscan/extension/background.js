const API_URL = "https://tefinitely.com/folscan/backend/api"; // Replace with actual EC2 URL

chrome.runtime.onInstalled.addListener(() => {
    // Check license periodically or on startup
    chrome.storage.local.get(["licenseKey"], (data) => {
        if (data.licenseKey) {
            validateLicense(data.licenseKey);
        }
    });
});

chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.action === "validateLicense") {
        validateLicense(request.licenseKey).then(sendResponse);
        return true;
    }
});

async function validateLicense(licenseKey) {
    if (licenseKey === "MOCK-PREMIUM-KEY") {
        await chrome.storage.local.set({ isPremium: true, licenseKey: licenseKey });
        return { success: true };
    }
    try {
        const response = await fetch(`${API_URL}/validate.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ license_key: licenseKey })
        });
        const data = await response.json();
        if (data.status === "active") {
            await chrome.storage.local.set({ isPremium: true, licenseKey: licenseKey });
            return { success: true };
        } else {
            await chrome.storage.local.set({ isPremium: false });
            return { success: false, message: "Invalid or inactive license key." };
        }
    } catch (error) {
        return { success: false, message: "Error connecting to server." };
    }
}
