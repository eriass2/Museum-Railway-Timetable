import { test, expect } from '@playwright/test';

test.describe('Timetable overview (static mount)', () => {
  test('loads overview from JSON AJAX', async ({ page }) => {
    await page.goto('/overview');
    await expect(page.locator('.mrt-vue-overview .mrt-ov')).toBeVisible();
    await expect(page.locator('.mrt-ov-banner')).toContainText('GRÖN TIDTABELL');
    await expect(page.locator('.mrt-ov-print-key-title')).toContainText('Förklaringar');
  });
});
