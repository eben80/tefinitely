import asyncio
from playwright.async_api import async_playwright, expect

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()

        # Navigate to the local index.html file
        await page.goto("file:///app/index.html")

        # Wait for the landing page to be visible
        await expect(page.locator("#login-prompt")).to_be_visible()

        # Take a screenshot
        await page.screenshot(path="jules-scratch/verification/redesign.png")

        await browser.close()

asyncio.run(main())
