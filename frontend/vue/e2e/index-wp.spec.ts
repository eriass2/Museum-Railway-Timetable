import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';

test.describe('Timetable index (WordPress)', () => {
  test.skip(!wpDemoUrl, 'Set MRT_E2E_WP_DEMO_URL');

  test('shortcode mounts Vue timetable index list', async ({ page }) => {
    await page.goto(wpDemoUrl!);
    const index = page.locator('.mrt-timetable-index').first();
    await expect(index).toBeVisible({ timeout: 20_000 });
    await expect(index.locator('.mrt-timetable-index__item').first()).toBeVisible();
  });
});
