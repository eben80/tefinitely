from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # --- Verify Section A ---
    page.goto("http://localhost:8000/index.html")
    page.evaluate("""
        document.getElementById('main-content').style.display = 'block';
        populateTopics(document.getElementById('topicSelect'));
    """)
    section_a_option = page.locator("select#topicSelect > option[value='Section_A-ask-for-directions-A1']")
    section_a_option.wait_for(timeout=10000)
    page.screenshot(path="jules-scratch/verification/section_a_dropdown.png")

    # --- Verify Section B ---
    page.goto("http://localhost:8000/section_b.html")
    page.evaluate("""
        document.getElementById('main-content').style.display = 'block';
        populateTopics(document.getElementById('topicSelect'));
    """)
    section_b_option = page.locator("select#topicSelect > option[value='Section_B-convince-a-friend-to-adopt-a-pet-B1']")
    section_b_option.wait_for(timeout=10000)
    page.screenshot(path="jules-scratch/verification/section_b_dropdown.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
