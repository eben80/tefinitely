const { test, expect } = require('@playwright/test');

test('Verify timer, counter color and next button', async ({ page }) => {
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
        advertisement: 'ANNONCE: Test',
        assistant: 'Bonjour !'
      }),
    });
  });

  // Mock continue session
  await page.route('**/practise/api/continue_session.php', route => {
    route.fulfill({
      contentType: 'application/json',
      body: JSON.stringify({
        assistant: 'Réponse',
        suggestion: ''
      }),
    });
  });

  await page.goto('http://localhost:8000/practise/section_a/index.php');
  await page.click('#start-btn');

  // Timer should be at 05:00
  await expect(page.locator('#timer-display')).toHaveText('05:00');
  // Badge should not be green
  const badge = page.locator('#question-count');
  await expect(badge).not.toHaveClass(/target-reached/);

  // Send first question
  await page.fill('#user-input', 'Question 1');
  await page.click('#send-btn');

  // Timer should start (check if it is no longer 05:00)
  await page.waitForTimeout(1100);
  const timerText = await page.locator('#timer-display').innerText();
  expect(timerText).not.toBe('05:00');

  // Send up to 10 questions
  for (let i = 2; i <= 10; i++) {
    await page.fill('#user-input', 'Question ' + i);
    await page.click('#send-btn');
  }

  // Badge should be green now
  await expect(badge).toHaveClass(/target-reached/);
  // Get color of badge
  const color = await badge.evaluate(el => window.getComputedStyle(el).backgroundColor);
  expect(color).toBe('rgb(40, 167, 69)'); // #28a745

  // Click Next Scenario
  await page.click('#next-btn');
  // Counter should reset to 0
  await expect(badge).toHaveText('0');
  // Badge should not be green anymore
  await expect(badge).not.toHaveClass(/target-reached/);
  // Timer should be reset to 05:00
  await expect(page.locator('#timer-display')).toHaveText('05:00');

  await page.screenshot({ path: 'timer_and_next_check.png' });
});
