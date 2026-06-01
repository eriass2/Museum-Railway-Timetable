import { test, expect } from '@playwright/test';
import { adminNavLink, useAdminMobileViewport } from './admin-helpers';

const adminUrl = '/admin?page=mrt_app';

async function mountAdmin(page: import('@playwright/test').Page) {
  await useAdminMobileViewport(page);
  await page.goto(adminUrl);
  await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
  await expect(page.locator('.mrt-admin-nav')).toBeVisible();
  await expect(page.getByRole('heading', { name: /museum railway timetable/i })).toBeVisible({
    timeout: 15_000,
  });
  await page.evaluate(() => {
    (window as unknown as { __mrtE2eStay?: boolean }).__mrtE2eStay = true;
  });
}

async function expectStillSpa(page: import('@playwright/test').Page) {
  await expect
    .poll(async () =>
      page.evaluate(() => (window as unknown as { __mrtE2eStay?: boolean }).__mrtE2eStay),
    )
    .toBe(true);
}

test.describe('AdminNav integration (static mount)', () => {
  test('switches tabs via hash without full page reload', async ({ page }) => {
    await mountAdmin(page);

    const steps = [
      { tab: 'Stationer', hash: '#/stations-routes', heading: /stationer & rutter/i },
      { tab: 'Tidtabeller', hash: '#/timetables', heading: /^tidtabeller$/i },
      { tab: 'Hjälp', hash: '#/help', heading: /^hjälp$/i },
      { tab: 'Priser', hash: '#/prices', heading: /^priser$/i },
      { tab: 'Översikt', hash: '#/dashboard', heading: /museum railway timetable/i },
    ];

    for (const step of steps) {
      await adminNavLink(page, step.tab).click();
      await expect(page).toHaveURL(new RegExp(step.hash.replace('#', '#')));
      await expect(page.getByRole('heading', { name: step.heading }).first()).toBeVisible({
        timeout: 15_000,
      });
      await expectStillSpa(page);
    }
  });

  test('updates admin.php page query without reload', async ({ page }) => {
    await mountAdmin(page);

    await adminNavLink(page, 'Import').click();
    await expect(page).toHaveURL(/page=mrt_app_import_export/);
    await expect(page).toHaveURL(/#\/import-export/);
    await expectStillSpa(page);

    await adminNavLink(page, 'Inställningar').click();
    await expect(page).toHaveURL(/page=mrt_app_settings/);
    await expect(page).toHaveURL(/#\/settings/);
    await expectStillSpa(page);
  });

  test('marks active nav tab', async ({ page }) => {
    await mountAdmin(page);

    const helpTab = adminNavLink(page, 'Hjälp');
    await helpTab.click();
    await expect(helpTab).toHaveClass(/nav-tab-active/);
    await expect(page.locator('.mrt-admin-nav a.nav-tab-active')).toHaveCount(1);
  });
});
