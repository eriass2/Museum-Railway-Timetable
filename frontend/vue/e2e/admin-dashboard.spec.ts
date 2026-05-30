import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';
import { loginWpAdmin } from './wp-admin-login';

const adminUrl =
  process.env.MRT_E2E_WP_ADMIN_URL ||
  (wpDemoUrl
    ? `${wpDemoUrl.match(/^(https?:\/\/[^/]+)/)?.[1] || ''}/wp-admin/admin.php?page=mrt_app`
    : '');

test.describe('Vue admin (WordPress)', () => {
  test.skip(!adminUrl, 'Set MRT_E2E_WP_ADMIN_URL or MRT_E2E_WP_DEMO_URL');

  test.beforeEach(async ({ page }) => {
    await loginWpAdmin(page);
  });

  test('dashboard mounts with stats', async ({ page }) => {
    await page.goto(adminUrl);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await expect(page.getByRole('heading', { name: /museum railway timetable/i })).toBeVisible();
    await expect(page.locator('.mrt-admin-stats')).toBeVisible({ timeout: 15_000 });
  });

  test('timetables nav loads list', async ({ page }) => {
    await page.goto(adminUrl);
    await page.locator('.mrt-admin-nav a', { hasText: 'Tidtabeller' }).click();
    await expect(page.getByRole('heading', { name: /tidtabeller/i })).toBeVisible({
      timeout: 15_000,
    });
  });
});
