import asyncio
from playwright.async_api import async_playwright

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        # iPhone 12 viewport
        context = await browser.new_context(
            viewport={'width': 390, 'height': 844},
            user_agent='Mozilla/5.0 (iPhone; CPU iPhone OS 14_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1'
        )
        page = await context.new_page()

        try:
            # We will use the mock session check by intercepting the request
            await page.route("**/api/check_session.php", lambda route: route.fulfill(
                status=200,
                content_type="application/json",
                body='{"status":"success","loggedIn":true,"user":{"user_id":1,"first_name":"Test","role":"user","subscription_status":"inactive"}}'
            ))

            # Also mock the paypal config
            await page.route("**/api/paypal/get_config.php", lambda route: route.fulfill(
                status=200,
                content_type="application/json",
                body='{"clientId":"test-client-id","currency":"CAD"}'
            ))

            # Mock paypal SDK
            await page.route("https://www.paypal.com/sdk/js*", lambda route: route.fulfill(
                status=200,
                content_type="application/javascript",
                body='window.paypal = { Buttons: function() { return { render: function(id) { document.querySelector(id).innerHTML = "MOCK PAYPAL BUTTONS"; } }; }, getFundingSources: function() { return ["paypal", "card", "googlepay", "applepay"]; }, FUNDING: { PAYPAL: "paypal", CARD: "card", GOOGLEPAY: "googlepay", APPLEPAY: "applepay" } };'
            ))

            # Load index.html
            await page.goto('http://localhost:8000/index.html')

            # Wait for JS
            await asyncio.sleep(2)

            # Scroll to subscription prompt
            await page.evaluate("document.getElementById('subscription-prompt').scrollIntoView()")
            await asyncio.sleep(1)

            # Take a screenshot
            await page.screenshot(path='/home/jules/verification/subscription_prompt_mobile.png')

            # Check visibility
            is_visible = await page.is_visible("#subscription-prompt")
            print(f"Subscription Prompt visible: {is_visible}")

        except Exception as e:
            print(f"Error: {e}")
        finally:
            await browser.close()

if __name__ == "__main__":
    asyncio.run(run())
