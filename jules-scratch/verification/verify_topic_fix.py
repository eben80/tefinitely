
from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    page = browser.new_page()

    # Navigate to the page
    page.goto("http://localhost:8000/oral_expression_section_a.html", wait_until="networkidle")

    # The check_session.php call might fail in this test environment,
    # so we'll directly make the UI visible to test the topic population.
    page.evaluate("document.getElementById('main-content').style.display = 'block'")

    # Manually trigger the populateTopics function
    page.evaluate("populateTopics(document.getElementById('topicSelect'))")


    # Wait for the topic select element to be populated with at least one option
    topic_select = page.locator("#topicSelect > option")
    expect(topic_select.first).to_be_enabled()

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/topic_fix.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
