import { test, expect } from '@playwright/test';

const wpMonthUrl = process.env.MRT_E2E_WP_MONTH_URL;

test.describe('Month calendar (WordPress)', () => {
  test.skip(!wpMonthUrl, 'Set MRT_E2E_WP_MONTH_URL to a page with [mrt_month_calendar]');

  test('shortcode mounts month grid', async ({ page }) => {
    await page.goto(wpMonthUrl!);
    await expect(page.locator('.mrt-month')).toBeVisible({ timeout: 15_000 });
    await expect(page.locator('.mrt-calendar-grid--month')).toBeVisible();
  });
});
