from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # --- Verify Section A ---
    page.goto("http://localhost:8000/index.html")

    # Make content visible and trigger the async topic population
    page.evaluate("""
        document.getElementById('main-content').style.display = 'block';
        populateTopics(document.getElementById('topicSelect'));
    """)

    # Wait for the specific option to appear in the DOM
    section_a_option = page.locator("select#topicSelect > option[value='Section_A-ask-for-directions-A1']")
    section_a_option.wait_for(timeout=10000)

    # Now select the option and take the screenshot
    page.select_option("select#topicSelect", "Section_A-ask-for-directions-A1")
    expect(page.locator("#phraseBox")).to_be_visible()
    page.screenshot(path="jules-scratch/verification/section_a.png")

    # --- Verify Section B ---
    page.goto("http://localhost:8000/section_b.html")

    # Repeat the process for Section B
    page.evaluate("""
        document.getElementById('main-content').style.display = 'block';
        populateTopics(document.getElementById('topicSelect'));
    """)

    section_b_option = page.locator("select#topicSelect > option[value='Section_B-convince-a-friend-to-adopt-a-pet-B1']")
    section_b_option.wait_for(timeout=10000)

    page.select_option("select#topicSelect", "Section_B-convince-a-friend-to-adopt-a-pet-B1")
    expect(page.locator("#phraseBox")).to_be_visible()
    page.screenshot(path="jules-scratch/verification/section_b.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
