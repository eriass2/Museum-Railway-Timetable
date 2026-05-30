import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';
import { loginWpAdmin } from './wp-admin-login';

const adminUrl =
  process.env.MRT_E2E_WP_ADMIN_URL ||
  (wpDemoUrl
    ? `${wpDemoUrl.match(/^(https?:\/\/[^/]+)/)?.[1] || ''}/wp-admin/admin.php?page=mrt_app`
    : '');

test.describe('Vue admin timetable flow', () => {
  test.skip(!adminUrl, 'Set MRT_E2E_WP_ADMIN_URL or MRT_E2E_WP_DEMO_URL');

  test.beforeEach(async ({ page }) => {
    await loginWpAdmin(page);
  });

  test('create timetable, add trip, save stop time', async ({ page }) => {
    const uniqueTitle = `E2E ${Date.now()}`;
    const trafficDate = '2099-12-24';

    await page.goto(adminUrl);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });

    await page.locator('.mrt-admin-nav a', { hasText: 'Tidtabeller' }).click();
    await expect(page.getByRole('heading', { name: /tidtabeller/i })).toBeVisible({
      timeout: 15_000,
    });

    await page.getByPlaceholder('Namn').fill(uniqueTitle);
    await page.getByRole('button', { name: 'Skapa' }).click();
    await expect(page.locator('#mrt-tt-title')).toHaveValue(uniqueTitle, { timeout: 15_000 });

    await page.locator('.nav-tab', { hasText: 'Trafikdagar' }).click();
    await page.locator('input[type="date"]').fill(trafficDate);
    await page.getByRole('button', { name: 'Lägg till datum' }).click();
    await page.getByRole('button', { name: 'Spara' }).first().click();
    await expect(page.getByText('Trafikdagar sparade')).toBeVisible({ timeout: 10_000 });

    await page.locator('.nav-tab', { hasText: 'Turer' }).click();
    const routeSelect = page.locator('.mrt-admin-trip-form select').first();
    await expect(routeSelect).toBeVisible({ timeout: 10_000 });
    const routeOptions = routeSelect.locator('option');
    const routeCount = await routeOptions.count();
    if (routeCount < 2) {
      test.skip(true, 'No routes in database — import demo data first');
    }
    await routeSelect.selectOption({ index: 1 });
    const destSelect = page.locator('.mrt-admin-trip-form select').nth(1);
    if ((await destSelect.locator('option').count()) > 1) {
      await destSelect.selectOption({ index: 1 });
    }
    await page.getByRole('button', { name: 'Lägg till tur' }).click();
    await expect(page.locator('.widefat.striped tbody tr').first()).toBeVisible({
      timeout: 15_000,
    });

    await page.locator('.nav-tab', { hasText: 'Stopptider' }).click();
    await page.getByText('Tabellvy för en tur').click();
    const serviceSelect = page.locator('summary').locator('..').locator('select').first();
    await serviceSelect.selectOption({ index: 1 });
    const depInput = page.locator('.mrt-admin-stoptimes input[type="time"]').first();
    await expect(depInput).toBeVisible({ timeout: 15_000 });
    await depInput.fill('09:30');
    await page.getByRole('button', { name: 'Spara stopptider' }).click();
    await expect(page.getByText('Stopptider sparade')).toBeVisible({ timeout: 15_000 });
  });
});
