import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';

test.describe('Timetable overview (WordPress)', () => {
  test.skip(!wpDemoUrl, 'Set MRT_E2E_WP_DEMO_URL (or legacy MRT_E2E_WP_OVERVIEW_URL)');

  test('shortcode mounts Vue overview with timetable grid', async ({ page }) => {
    await page.goto(wpDemoUrl!);
    const section = page.locator('h2', { hasText: /timetable overview|tidtabellsöversikt/i });
    if (await section.count()) {
      await section.first().scrollIntoViewIfNeeded();
    }
    const overview = page.locator('.mrt-vue-overview').first();
    await expect(overview).toBeVisible({ timeout: 20_000 });
    await expect(overview.locator('.mrt-ov')).toBeVisible({ timeout: 20_000 });
  });
});
