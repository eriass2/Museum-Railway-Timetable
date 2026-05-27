import { test, expect } from '@playwright/test';

const wpUrl = process.env.MRT_E2E_WP_URL;

test.describe('Journey wizard (WordPress)', () => {
  test.skip(!wpUrl, 'Set MRT_E2E_WP_URL (e.g. http://127.0.0.1:8080/?page_id=569)');

  test('demo page mounts wizard', async ({ page }) => {
    await page.goto(wpUrl!);
    const root = page.locator('.mrt-journey-wizard');
    await expect(root).toBeVisible({ timeout: 15_000 });
    await expect(page.locator('.mrt-journey-wizard__nav')).toBeVisible();
  });
});
