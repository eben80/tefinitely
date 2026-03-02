const { chromium, devices } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext(devices['iPhone 12']);
  const page = await context.newPage();

  // Go to index.html
  await page.goto('http://localhost:8000/index.html');

  // Wait for some time for auth.js to run
  await page.waitForTimeout(2000);

  // Check visibility of elements
  const visibility = await page.evaluate(() => {
    const getStyle = (id) => {
      const el = document.getElementById(id);
      return el ? {
        id,
        display: window.getComputedStyle(el).display,
        visibility: window.getComputedStyle(el).visibility,
        opacity: window.getComputedStyle(el).opacity,
        height: el.offsetHeight,
        width: el.offsetWidth,
        classes: Array.from(el.classList)
      } : { id, error: 'not found' };
    };

    return {
      authContainer: getStyle('auth-container'),
      loginPrompt: getStyle('login-prompt'),
      subscriptionPrompt: getStyle('subscription-prompt'),
      userStatus: getStyle('user-status'),
      landingNav: getStyle('landing-nav'),
      landingFooter: getStyle('landing-footer')
    };
  });

  console.log('Visibility:', JSON.stringify(visibility, null, 2));

  await page.screenshot({ path: '/home/jules/verification/debug_mobile_logged_out.png', fullPage: true });

  await browser.close();
})();
