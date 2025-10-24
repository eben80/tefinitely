
from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    page = browser.new_page()

    # Navigate to the page
    page.goto("http://localhost:8000/index.html", wait_until="networkidle")

    # Wait for the async session check to complete and the login prompt to be displayed.
    login_prompt = page.locator("#login-prompt")
    expect(login_prompt).to_be_visible()

    # Add a small delay to ensure the JavaScript in the page has time to run
    page.wait_for_timeout(1000)

    # Now that the login prompt is visible, the sample flashcard inside it should also be visible.
    sample_flashcard = page.locator("#sample-flashcard-container")
    expect(sample_flashcard).to_be_visible()

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/sample_flashcard.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
