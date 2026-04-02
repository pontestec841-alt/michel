from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()

        # Test mobile responsiveness and mute button on invitation page
        context = browser.new_context(viewport={'width': 375, 'height': 667}) # iPhone SE size
        page = context.new_page()

        # Need to login first
        page.goto("http://localhost:3000/index.php")
        page.fill("input[name='guest_name']", "Test Guest")
        page.click("button[type='submit']")

        # We should be on gifts page now. Take a screenshot
        page.wait_for_load_state("networkidle")
        page.screenshot(path="verification_screenshots/gifts_page_mobile.png")

        # Click the skip link
        page.click("text='Pular e ver meu convite'")

        # We should be on invitation page. Take a screenshot
        page.wait_for_load_state("networkidle")
        page.screenshot(path="verification_screenshots/invitation_page_mobile.png")

        browser.close()

if __name__ == "__main__":
    run()
