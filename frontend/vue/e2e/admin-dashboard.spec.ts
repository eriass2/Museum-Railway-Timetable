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

  test('stations routes shows route preview', async ({ page }) => {
    await page.goto(adminUrl);
    await page.locator('.mrt-admin-nav a', { hasText: 'Stationer' }).click();
    await expect(page.getByRole('heading', { name: /stationer & rutter/i })).toBeVisible({
      timeout: 15_000,
    });
    await expect(page.locator('.mrt-route-preview').first()).toBeVisible({ timeout: 15_000 });
  });

  test('mobile dashboard shows stat cards', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(adminUrl);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await expect(page.locator('.mrt-admin-stat-grid')).toBeVisible({ timeout: 15_000 });
    await expect(page.locator('.mrt-admin-stat-card').first()).toBeVisible();
  });

  test('mobile timetable list shows cards', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(adminUrl);
    await page.locator('.mrt-admin-nav a', { hasText: 'Tidtabeller' }).click();
    await expect(page.getByRole('heading', { name: /tidtabeller/i })).toBeVisible({
      timeout: 15_000,
    });
    const cards = page.locator('.mrt-admin-card-list__item');
    const table = page.locator('table.widefat');
    if ((await cards.count()) > 0) {
      await expect(cards.first()).toBeVisible();
    } else {
      await expect(table.or(page.locator('.mrt-admin-card-list__empty'))).toBeVisible();
    }
  });

  test('mobile timetable editor shows quick departure panel', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(adminUrl);
    await page.locator('.mrt-admin-nav a', { hasText: 'Tidtabeller' }).click();
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
