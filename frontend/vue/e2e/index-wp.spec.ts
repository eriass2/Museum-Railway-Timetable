import { test, expect } from '@playwright/test';
import { wpIndexUrl } from './wp-demo-url';

test.describe('Timetable index (WordPress)', () => {
  test.skip(!wpIndexUrl, 'Set MRT_E2E_WP_INDEX_URL or MRT_E2E_WP_DEMO_URL');

  test('shortcode mounts Vue timetable index list', async ({ page }) => {
    await page.goto(wpIndexUrl);
    const index = page.locator('.mrt-timetable-index').first();
    await expect(index).toBeVisible({ timeout: 20_000 });
    await expect(index.locator('.mrt-timetable-index__item').first()).toBeVisible();
  });
});
