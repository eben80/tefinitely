const statusDiv = document.getElementById("status");
const licenseForm = document.getElementById("license-form");
const activeInfo = document.getElementById("active-info");
const licenseInp = document.getElementById("license-key");
const activateBtn = document.getElementById("activate-btn");
const deactivateBtn = document.getElementById("deactivate-btn");


chrome.storage.local.get(["isPremium", "licenseKey"], (data) => {
    if (data.isPremium) {
        statusDiv.innerText = "Premium status active.";
        activeInfo.style.display = "block";
    } else {
        statusDiv.innerText = "Free plan limited.";
        licenseForm.style.display = "block";
    }
});

activateBtn.addEventListener("click", async () => {
    const key = licenseInp.value.trim();
    if (!key) return;

    statusDiv.innerText = "Activating...";
    chrome.runtime.sendMessage({ action: "validateLicense", licenseKey: key }, (response) => {
        if (response.success) {
            statusDiv.innerText = "License activated!";
            licenseForm.style.display = "none";
            activeInfo.style.display = "block";
            location.reload();
        } else {
            statusDiv.innerText = "Activation failed: " + response.message;
        }
    });
});

deactivateBtn.addEventListener("click", () => {
    chrome.storage.local.set({ isPremium: false, licenseKey: null }, () => {
        location.reload();
    });
});
