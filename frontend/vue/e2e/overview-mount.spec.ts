import { test, expect } from '@playwright/test';

test.describe('Timetable overview (static mount)', () => {
  test('loads overview from JSON AJAX with rail and branch groups', async ({ page }) => {
    await page.goto('/overview');
    await expect(page.locator('.mrt-vue-overview .mrt-ov')).toBeVisible();
    await expect(page.locator('.mrt-ov-banner')).toContainText('GRÖN TIDTABELL');
    await expect(page.locator('.mrt-ov-route-title').first()).toContainText('Uppsala Östra – Faringe');
    await expect(page.locator('.mrt-ov-group--branch .mrt-ov-route-title')).toContainText(
      'Selknä – Uppsala Östra',
    );
    await expect(page.locator('.mrt-ov-branch-table tbody tr')).toHaveCount(1);
    await expect(page.locator('.mrt-ov-print-key-title')).toContainText('Förklaringar');
  });
});
