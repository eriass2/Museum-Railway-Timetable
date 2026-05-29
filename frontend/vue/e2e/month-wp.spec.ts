import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';

test.describe('Month calendar (WordPress)', () => {
  test.skip(!wpDemoUrl, 'Set MRT_E2E_WP_DEMO_URL (or legacy MRT_E2E_WP_MONTH_URL)');

  test('shortcode mounts month grid on demo page', async ({ page }) => {
    await page.goto(wpDemoUrl!);
    const section = page.locator('h2', { hasText: /month calendar|månadskalender/i });
    if (await section.count()) {
      await section.first().scrollIntoViewIfNeeded();
    }
    await expect(page.locator('.mrt-month').first()).toBeVisible({ timeout: 20_000 });
    await expect(page.locator('.mrt-calendar-grid--month').first()).toBeVisible();
  });
});
