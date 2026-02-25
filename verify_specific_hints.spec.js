const { test, expect } = require('@playwright/test');

test('Verify specific hint functionality', async ({ page }) => {
  // Mock session check
  await page.route('**/api/check_session.php', route => {
    route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({ loggedIn: true, user: { subscription_status: 'active' } }),
    });
  });

  // Mock start session
  await page.route('**/practise/api/start_session.php', route => {
    route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        instruction: 'CONSIGNE: Test',
        advertisement: 'ANNONCE: Test spécifique',
        assistant: 'Bonjour !'
      }),
    });
  });

  // Mock get hints
  await page.route('**/practise/api/get_hints.php', route => {
    route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        hints: [
          'Question spécifique 1 ?',
          'Question spécifique 2 ?'
        ]
      }),
    });
  });

  await page.goto('http://localhost:8000/practise/index.html');
  await page.click('#start-btn');
  await page.click('#hint-btn');
  await expect(page.locator('#hints-modal')).toBeVisible();
  const hints = page.locator('#hints-list li');
  await expect(hints).toHaveCount(2);
  await expect(hints.first()).toHaveText('Question spécifique 1 ?');
  await page.screenshot({ path: 'specific_hints_check.png' });
});
