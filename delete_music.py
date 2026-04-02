from playwright.sync_api import sync_playwright
import os

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()

        # Login to Admin panel
        page.goto("http://localhost:3000/admin.php")
        page.fill("input[name='password']", "admin123")
        page.click("button[name='admin_login']")

        # Verify Delete Music button is visible
        if page.locator("button[name='delete_music']").is_visible():
            print("Delete Music button found.")

            # Click and handle confirm dialog
            page.on("dialog", lambda dialog: dialog.accept())
            page.click("button[name='delete_music']")

            # Check success message
            if "Música excluída com sucesso!" in page.content():
                print("Music deleted successfully from UI.")
                page.screenshot(path="verification_screenshots/admin_music_deleted.png")
            else:
                print("Success message not found.")
        else:
            print("Delete Music button NOT found.")

        browser.close()

if __name__ == "__main__":
    run()
