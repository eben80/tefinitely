from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    page.goto("http://localhost:8000/index.html")

    # The main content is hidden. We make it visible and trigger topic population,
    # then wait for the network request to finish.
    with page.expect_response("**/api/get_topics.php"):
        page.evaluate("""
            document.getElementById('main-content').style.display = 'block';
            populateTopics(document.getElementById('topicSelect'));
        """)

    # Now that topics are loaded, we can select one and take screenshots.
    page.select_option("select#topicSelect", "Section_A-ask-for-directions")
    expect(page.locator("#phraseBox")).to_be_visible()

    # Press Right Arrow and screenshot
    page.press('body', 'ArrowRight')
    page.screenshot(path="jules-scratch/verification/right_arrow.png")

    # Press Space Bar and screenshot
    page.press('body', ' ')
    page.screenshot(path="jules-scratch/verification/space_bar.png")

    # Press Left Arrow and screenshot
    page.press('body', 'ArrowLeft')
    page.screenshot(path="jules-scratch/verification/left_arrow.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
