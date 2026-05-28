import { test, expect } from '@playwright/test';

const wpOverviewUrl = process.env.MRT_E2E_WP_OVERVIEW_URL;

test.describe('Timetable overview (WordPress)', () => {
  test.skip(!wpOverviewUrl, 'Set MRT_E2E_WP_OVERVIEW_URL to a page with the overview shortcode');

  test('shortcode mounts overview panel', async ({ page }) => {
    await page.goto(wpOverviewUrl!);
    await expect(page.locator('.mrt-vue-overview')).toBeVisible({ timeout: 15_000 });
    await expect(page.locator('.mrt-vue-overview .mrt-html-panel')).toBeVisible();
  });
});
