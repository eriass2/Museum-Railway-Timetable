import { test, expect } from '@playwright/test';

test.describe('Timetable overview (static mount)', () => {
  test('loads overview HTML from AJAX', async ({ page }) => {
    await page.goto('/overview');
    await expect(page.locator('.mrt-vue-overview')).toBeVisible();
    await expect(page.locator('.mrt-vue-overview')).toContainText('Översikt tidtabell');
  });
});
