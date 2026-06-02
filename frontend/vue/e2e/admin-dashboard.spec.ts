import { test, expect } from '@playwright/test';
import { adminNavLink, gotoAdminRoute, useAdminMobileViewport, DASHBOARD_HEADING } from './admin-helpers';
import { wpDemoUrl } from './wp-demo-url';
import { loginWpAdmin } from './wp-admin-login';

const adminUrl =
  process.env.MRT_E2E_WP_ADMIN_URL ||
  (wpDemoUrl
    ? `${wpDemoUrl.match(/^(https?:\/\/[^/]+)/)?.[1] || ''}/wp-admin/admin.php?page=mrt_app`
    : '');

test.describe('Vue admin (WordPress)', () => {
  test.describe.configure({ mode: 'serial' });
  test.skip(!adminUrl, 'Set MRT_E2E_WP_ADMIN_URL or MRT_E2E_WP_DEMO_URL');

  test.beforeEach(async ({ page }) => {
    await loginWpAdmin(page);
  });

  test('dashboard mounts with stats', async ({ page }) => {
    await page.goto(adminUrl);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await expect(page.getByRole('heading', { name: DASHBOARD_HEADING })).toBeVisible({
      timeout: 15_000,
    });
    await expect(page.locator('.mrt-admin-stats')).toBeVisible({ timeout: 15_000 });
  });

  test('timetables route loads list', async ({ page }) => {
    await gotoAdminRoute(page, adminUrl, '/timetables');
    await expect(page.getByRole('heading', { name: /tidtabeller/i })).toBeVisible({
      timeout: 15_000,
    });
  });

  test('stations routes shows route preview', async ({ page }) => {
    await gotoAdminRoute(page, adminUrl, '/stations-routes');
    await expect(page.getByRole('heading', { name: /stationer & rutter/i })).toBeVisible({
      timeout: 15_000,
    });
    await page.locator('.nav-tab', { hasText: /rutter/i }).click();
    const preview = page.locator('.mrt-route-preview').first();
    const empty = page.locator('.mrt-route-preview__empty');
    await expect(preview.or(empty)).toBeVisible({ timeout: 15_000 });
    if ((await preview.count()) === 0) {
      test.skip(true, 'No routes in database — import demo data first');
    }
  });

  test('mobile dashboard shows stat cards', async ({ page }) => {
    await useAdminMobileViewport(page);
    await page.goto(adminUrl);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await expect(page.locator('.mrt-admin-stat-grid')).toBeVisible({ timeout: 15_000 });
    await expect(page.locator('.mrt-admin-stat-card').first()).toBeVisible();
  });

  test('mobile timetable list shows cards', async ({ page }) => {
    await useAdminMobileViewport(page);
    await page.goto(adminUrl);
    await adminNavLink(page, 'Tidtabeller').click();
    await expect(page.getByRole('heading', { name: /tidtabeller/i })).toBeVisible({
      timeout: 15_000,
    });
    const cards = page.locator('.mrt-admin-card-list__item');
    await expect(cards.first().or(page.getByText(/inga tidtabeller/i))).toBeVisible({
      timeout: 15_000,
    });
  });

  test('mobile timetable editor shows quick departure panel', async ({ page }) => {
    await useAdminMobileViewport(page);
    await page.goto(adminUrl);
    await adminNavLink(page, 'Tidtabeller').click();
    await expect(page.getByRole('heading', { name: /tidtabeller/i })).toBeVisible({
      timeout: 15_000,
    });
    const editBtn = page.getByRole('button', { name: 'Redigera' }).first();
    if ((await editBtn.count()) === 0) {
      test.skip(true, 'No timetables in database');
    }
    await editBtn.click();
    await expect(page.locator('.mrt-admin-mobile-panel')).toBeVisible({ timeout: 15_000 });
    await expect(page.getByRole('heading', { name: /snabb avgångstid/i })).toBeVisible();
  });
});
