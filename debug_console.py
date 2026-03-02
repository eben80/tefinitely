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

        # Listen for console messages
        page.on("console", lambda msg: print(f"CONSOLE {msg.type}: {msg.text}"))
        page.on("pageerror", lambda exc: print(f"PAGE ERROR: {exc}"))

        try:
            # Load index.html
            await page.goto('http://localhost:8000/index.html')

            # Wait for some time to let JS execute
            await asyncio.sleep(2)

            # Take a screenshot
            await page.screenshot(path='/home/jules/verification/mobile_console_check.png', full_page=True)

            # Check for visibility of key elements
            elements = {
                "header": "header",
                "#auth-container": "#auth-container",
                "#login-prompt": "#login-prompt",
                "#subscription-prompt": "#subscription-prompt",
                "#landing-footer": "#landing-footer"
            }

            for name, selector in elements.items():
                try:
                    is_visible = await page.is_visible(selector)
                    display = await page.evaluate(f"window.getComputedStyle(document.querySelector('{selector}')).display") if await page.query_selector(selector) else "N/A"
                    print(f"{name}: visible={is_visible}, display={display}")
                except Exception as e:
                    print(f"{name}: Error finding element: {e}")

        except Exception as e:
            print(f"Error: {e}")
        finally:
            await browser.close()

if __name__ == "__main__":
    asyncio.run(run())
