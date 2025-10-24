from playwright.sync_api import sync_playwright

def run_verification(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    page.goto("http://localhost:8000/logged_in.html")

    # The nav bar is hidden by default, so we'll make it visible
    page.evaluate("document.getElementById('user-status').style.display = 'flex'")

    page.screenshot(path="jules-scratch/verification/logged_in_dashboard.png")

    browser.close()

with sync_playwright() as playwright:
    run_verification(playwright)
