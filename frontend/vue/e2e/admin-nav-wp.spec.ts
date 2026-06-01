import { test, expect } from '@playwright/test';
import { adminNavLink, useAdminMobileViewport } from './admin-helpers';
import { wpDemoUrl } from './wp-demo-url';
import { loginWpAdmin } from './wp-admin-login';

const adminUrl =
  process.env.MRT_E2E_WP_ADMIN_URL ||
  (wpDemoUrl
    ? `${wpDemoUrl.match(/^(https?:\/\/[^/]+)/)?.[1] || ''}/wp-admin/admin.php?page=mrt_app`
    : '');

async function expectStillSpa(page: import('@playwright/test').Page) {
  await expect
    .poll(async () =>
      page.evaluate(() => (window as unknown as { __mrtE2eStay?: boolean }).__mrtE2eStay),
    )
    .toBe(true);
}

test.describe('AdminNav integration (WordPress)', () => {
  test.skip(!adminUrl, 'Set MRT_E2E_WP_ADMIN_URL or MRT_E2E_WP_DEMO_URL');

  test.beforeEach(async ({ page }) => {
    await loginWpAdmin(page);
    await useAdminMobileViewport(page);
    await page.goto(adminUrl);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await expect(page.locator('.mrt-admin-shell__nav')).toBeVisible();
    await page.evaluate(() => {
      (window as unknown as { __mrtE2eStay?: boolean }).__mrtE2eStay = true;
    });
  });

  test('nav tabs switch view without full page reload', async ({ page }) => {
    await adminNavLink(page, 'Tidtabeller').click();
    await expect(page).toHaveURL(/#\/timetables/);
    await expect(page.getByRole('heading', { name: /^tidtabeller$/i })).toBeVisible({
      timeout: 15_000,
    });
    await expectStillSpa(page);

    await adminNavLink(page, 'Hjälp').click();
    await expect(page).toHaveURL(/#\/help/);
    await expect(page.getByRole('heading', { name: /^hjälp$/i })).toBeVisible({
      timeout: 15_000,
    });
    await expectStillSpa(page);

    await adminNavLink(page, 'Översikt').click();
    await expect(page).toHaveURL(/#\/dashboard/);
    await expect(page.getByRole('heading', { name: /museum railway timetable/i })).toBeVisible({
      timeout: 15_000,
    });
    await expectStillSpa(page);
  });

  test('does not nest plugin path on tab navigation', async ({ page }) => {
    await adminNavLink(page, 'Priser').click();
    await expect(page).toHaveURL(/#\/prices/);
    await expect(page).not.toHaveURL(/museum-railway-timetable\/museum-railway-timetable/);
    await expectStillSpa(page);
  });
});
