
from playwright.sync_api import sync_playwright, expect
import time

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    page = browser.new_page()

    # Add a listener for browser console messages
    page.on("console", lambda msg: print(f"BROWSER CONSOLE: {msg.text}"))

    try:
        # Navigate to the page
        page.goto("http://localhost:8000/oral_expression_section_a.html", wait_until="networkidle")

        # Make the main content visible to bypass session checks
        page.evaluate("document.getElementById('main-content').style.display = 'block'")

        # Manually trigger the async topic population
        page.evaluate("populateTopics(document.getElementById('topicSelect'))")

        # Wait specifically for the first expected topic to be attached to the DOM.
        # This is a more reliable check than asserting the full count.
        first_topic_option = page.locator('option[value="A-an_online_store_that_sells_eyeglasses"]')
        expect(first_topic_option).to_be_attached(timeout=10000)
        print("Verification successful: Topics have started loading.")

        # Now that we know topics are loading, select the first one
        page.select_option("#topicSelect", "A-an_online_store_that_sells_eyeglasses")

        # Wait for the phrase box to become visible
        phrase_box = page.locator("#phraseBox")
        expect(phrase_box).to_be_visible(timeout=10000)

        # Verify that the phrase content is not empty
        english_phrase = page.locator("#phraseEnglish")
        expect(english_phrase).not_to_be_empty(timeout=10000)

        print("Verification successful: Phrases loaded correctly.")

        # Take a final screenshot
        page.screenshot(path="jules-scratch/verification/phrases_loaded.png")

    except Exception as e:
        print(f"An error occurred during verification: {e}")
        page.screenshot(path="jules-scratch/verification/verification_error.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
