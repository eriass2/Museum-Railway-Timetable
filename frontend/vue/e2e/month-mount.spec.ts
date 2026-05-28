import { test, expect } from '@playwright/test';

test.describe('Month calendar (static mount)', () => {
  test('renders grid and opens day panel on click', async ({ page }) => {
    await page.goto('/month');
    const root = page.locator('.mrt-month');
    await expect(root).toBeVisible();
    await expect(page.locator('.mrt-calendar-grid--month')).toBeVisible();
    await expect(page.locator('.mrt-calendar-grid--month .mrt-day-clickable')).toHaveCount(2);

    await page.locator('.mrt-calendar-grid--month .mrt-day-clickable').first().click();
    await expect(page.locator('.mrt-html-panel')).toBeVisible();
    await expect(page.locator('.mrt-html-panel')).toContainText('Tidtabell');
  });
});
