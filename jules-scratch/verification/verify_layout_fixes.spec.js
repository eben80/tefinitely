const { test, expect } = require('@playwright/test');

test.describe('Mobile Layout Fixes', () => {
  test('Menu is positioned correctly and no horizontal scrollbar exists', async ({ page }) => {
    // Mock APIs
    await page.route('**/api/login.php', route => route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, message: 'Login successful' }) }));
    await page.route('**/api/check_session.php', route => route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ loggedIn: true, user: {first_name: "test", role: "user", subscription_status: "active"} }) }));
    await page.route('**/api/get_topics.php?section=A', route => route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ status: 'success', topics: { A: ["Sample Topic"] } }) }));
    await page.route('**/api/profile/get_progress.php', route => route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ status: 'success', progress: [] }) }));

    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });

    // Login and navigate
    await page.goto('http://localhost:8000/login.html');
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/logged_in.html');
    await page.goto('http://localhost:8000/oral_expression_section_a.html');

    // Open hamburger menu
    await page.locator('.hamburger-menu').click();

    // Take screenshot
    await page.screenshot({ path: 'jules-scratch/verification/layout_fixes_verified.png' });

    // Verify no horizontal scrollbar
    const hasScrollbar = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth);
    expect(hasScrollbar).toBe(false);
  });
});
