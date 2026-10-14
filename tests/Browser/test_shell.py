"""
Browser tests for the Lego shell (phase 4), with Playwright for Python.

They run in the official Playwright image against `php -S` with the test
modules (see compose.test.yml). No Node in the project (decisions B10, C6).

    docker compose -f compose.test.yml run --rm browser
"""
import os
import re
import sys
import traceback

from playwright.sync_api import expect, sync_playwright

BASE = os.environ.get("LEGO_URL", "http://web-test:8000")
EMAIL = "admin@example.com"
RUN = os.urandom(3).hex()
CREATED = f"Jardín {RUN}"
PASSWORD = "browser-password"

expect.set_options(timeout=10_000)


def url(path):
    return re.compile("^" + re.escape(BASE + path) + "$")


def screen(page, key):
    return page.locator(f'[data-lego-screen][data-lego-key="{key}"]')


def active(page):
    return page.locator("[data-lego-screen]:not([hidden])")


def test_login_redirects_back_to_the_requested_screen(page):
    page.goto(BASE + "/clients")
    expect(page).to_have_url(re.compile(r"/login\?next=%2Fclients$"))
    page.fill('input[name="email"]', EMAIL)
    page.fill('input[name="password"]', PASSWORD)
    page.click('button[type="submit"]')
    expect(page).to_have_url(url("/clients"))
    expect(active(page).locator("h1")).to_have_text("Clientes")
    page.wait_for_selector("html[data-lego-ready]", state="attached")
    page.evaluate("window.legoTestMarker = 42")


def test_navigates_between_modules_without_reloading(page):
    page.click('[data-lego-nav][data-module="reports"]')
    expect(page).to_have_url(url("/reports"))
    expect(active(page).locator("h1")).to_have_text("Informes")
    page.click('[data-lego-nav][data-module="clients"]')
    expect(page).to_have_url(url("/clients"))
    expect(active(page).locator("h1")).to_have_text("Clientes")
    expect(page.locator('[data-lego-nav][data-module="clients"]')).to_have_attribute("aria-current", "page")
    assert page.evaluate("window.legoTestMarker") == 42, "the page reloaded"


def test_back_and_forward_follow_the_url(page):
    page.go_back()
    expect(page).to_have_url(url("/reports"))
    expect(active(page).locator("h1")).to_have_text("Informes")
    page.go_forward()
    expect(page).to_have_url(url("/clients"))
    expect(active(page).locator("h1")).to_have_text("Clientes")
    assert page.evaluate("window.legoTestMarker") == 42, "the page reloaded"


def test_sorting_replaces_the_url_without_new_history(page):
    length = page.evaluate("history.length")
    active(page).locator("th a", has_text="Nombre").click()
    expect(page).to_have_url(re.compile(r"/clients\?sort=name&dir=asc$"))
    expect(active(page).locator("tbody tr").first).to_contain_text("Florería Lima")
    assert page.evaluate("history.length") == length, "sorting added a history entry"


def test_shows_server_validation_errors_in_the_form(page):
    active(page).locator("a", has_text="Nuevo").click()
    expect(page).to_have_url(url("/clients/new"))
    form = screen(page, "clients/new").locator('form[data-lego="form"]').first
    form.locator('input[name="email"]').fill("nope")
    form.locator('button[type="submit"]').click()
    expect(form.locator('[data-field="name"] .lego-field__error')).to_have_text("Nombre es obligatorio.")
    expect(form.locator('[data-field="email"] .lego-field__error')).to_have_text("Introduce un correo válido.")
    expect(form.locator('input[name="name"]')).to_have_attribute("aria-invalid", "true")


def test_creates_a_record_and_returns_to_the_updated_list(page):
    form = screen(page, "clients/new").locator('form[data-lego="form"]').first
    form.locator('input[name="name"]').fill(CREATED)
    form.locator('input[name="email"]').fill(f"jardin-{RUN}@central.co")
    form.locator('button[type="submit"]').click()
    expect(page).to_have_url(re.compile(r"/clients(\?.*)?$"))
    expect(active(page).locator("tbody")).to_contain_text(CREATED)
    expect(screen(page, "clients/new")).to_have_count(0)
    assert page.evaluate("window.legoTestMarker") == 42, "the page reloaded"


def test_keeps_unsaved_input_when_switching_screens(page):
    active(page).locator("a", has_text="Nuevo").click()
    draft = screen(page, "clients/new").locator('input[name="name"]')
    draft.fill("Borrador")
    expect(page.locator('.lego-shell__tab[data-key="clients/new"]')).to_have_attribute("data-dirty", "")
    page.click('[data-lego-nav][data-module="clients"]')
    active(page).locator("tbody a", has_text="Florería Lima").click()
    expect(page).to_have_url(re.compile(r"/clients/\d+$"))
    page.locator('.lego-shell__tab[data-key="clients/new"] a').click()
    expect(page).to_have_url(url("/clients/new"))
    expect(draft).to_have_value("Borrador")


def test_open_screens_never_share_element_ids(page):
    duplicates = page.evaluate(
        """() => {
            const ids = [...document.querySelectorAll('[id]')].map((e) => e.id);
            return ids.filter((id, i) => ids.indexOf(id) !== i);
        }"""
    )
    assert duplicates == [], f"duplicated ids: {duplicates}"
    assert page.locator("[data-lego-screen]").count() >= 3


def test_asks_before_closing_a_screen_with_unsaved_changes(page):
    page.locator('.lego-shell__tab[data-key="clients/new"] button').click()
    dialog = page.locator("dialog[data-lego-dialog]")
    expect(dialog).to_contain_text("cambios sin guardar")
    dialog.locator("button", has_text="Cancelar").click()
    expect(screen(page, "clients/new")).to_have_count(1)
    page.locator('.lego-shell__tab[data-key="clients/new"] button').click()
    page.locator("dialog[data-lego-dialog] [data-lego-confirm]").click()
    expect(screen(page, "clients/new")).to_have_count(0)


def test_searches_and_deletes_after_confirming_in_a_dialog(page):
    page.click('[data-lego-nav][data-module="clients"]')
    active(page).locator('input[name="q"]').fill(RUN)
    active(page).locator('form[role="search"] button').click()
    expect(page).to_have_url(re.compile(rf"/clients\?q={RUN}"))
    expect(active(page).locator("tbody tr")).to_have_count(1)
    active(page).locator("tbody a", has_text=CREATED).click()
    expect(page).to_have_url(re.compile(r"/clients/\d+$"))
    active(page).locator('form[data-lego-action="clients_delete"] button').click()
    dialog = page.locator("dialog[data-lego-dialog]")
    expect(dialog).to_contain_text(f"Se eliminará «{CREATED}».")
    dialog.locator("[data-lego-confirm]").click()
    expect(page).to_have_url(re.compile(r"/clients(\?.*)?$"))
    expect(active(page).locator("tbody")).not_to_contain_text(CREATED)


def test_reload_restores_only_the_active_screen(page):
    active(page).locator("tbody a", has_text="Florería Lima").click()
    expect(page).to_have_url(re.compile(r"/clients/\d+$"))
    page.reload()
    expect(active(page).locator('input[name="name"]')).to_have_value("Florería Lima")
    expect(page.locator("[data-lego-screen]")).to_have_count(1)
    expect(page.locator(".lego-shell__tab")).to_have_count(1)


def test_theme_toggle_switches_and_persists(page):
    before = page.evaluate("getComputedStyle(document.body).backgroundColor")
    page.click("[data-lego-theme-toggle]")
    after = page.evaluate("getComputedStyle(document.body).backgroundColor")
    assert before != after, "the theme did not change"
    page.reload()
    assert page.evaluate("getComputedStyle(document.body).backgroundColor") == after, "the theme was not kept"


def test_expired_session_sends_navigation_to_the_login(page):
    page.context.clear_cookies()
    # Home is not open yet, so the shell has to fetch it and gets a 401.
    page.click(".lego-shell__brand")
    expect(page).to_have_url(re.compile(r"/login\?next="))


TESTS = [
    test_login_redirects_back_to_the_requested_screen,
    test_navigates_between_modules_without_reloading,
    test_back_and_forward_follow_the_url,
    test_sorting_replaces_the_url_without_new_history,
    test_shows_server_validation_errors_in_the_form,
    test_creates_a_record_and_returns_to_the_updated_list,
    test_keeps_unsaved_input_when_switching_screens,
    test_open_screens_never_share_element_ids,
    test_asks_before_closing_a_screen_with_unsaved_changes,
    test_searches_and_deletes_after_confirming_in_a_dialog,
    test_reload_restores_only_the_active_screen,
    test_theme_toggle_switches_and_persists,
    test_expired_session_sends_navigation_to_the_login,
]


def main():
    failures = 0
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch()
        page = browser.new_page()
        js_errors = []
        page.on("pageerror", lambda error: js_errors.append(str(error)))
        page.on("console", lambda message: js_errors.append(message.text) if message.type == "error" else None)

        for test in TESTS:
            name = test.__name__.removeprefix("test_").replace("_", " ")
            try:
                test(page)
                print(f"  ✓ {name}")
            except Exception:
                failures += 1
                print(f"  ✗ {name}")
                print("    " + traceback.format_exc().strip().replace("\n", "\n    "))
                page.screenshot(path=f"/tmp/{test.__name__}.png")

        # Chrome logs every non-2xx response. 401 (expired session), 409, 422
        # (validation) and 428 (confirmation) are part of the protocol, not bugs.
        expected = re.compile(r"Failed to load resource: the server responded with a status of (401|409|422|428)\b")
        real_errors = [e for e in js_errors if not expected.search(e)]
        if real_errors:
            failures += 1
            print("  ✗ no JavaScript errors")
            for error in real_errors:
                print("    " + error)
        else:
            print("  ✓ no JavaScript errors")
        browser.close()

    print(f"\n  Browser tests: {len(TESTS) + 1 - failures} passed, {failures} failed")
    return 1 if failures else 0


if __name__ == "__main__":
    sys.exit(main())
