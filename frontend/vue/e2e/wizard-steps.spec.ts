import { test, expect } from '@playwright/test';

test.describe('Journey wizard steps', () => {
  test('date step via debug preset', async ({ page }) => {
    await page.goto('/wizard?debug=date');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'date');
    await expect(page.locator('[data-wizard-step="date"]')).toBeVisible();
    await expect(page.locator('.mrt-surface--flush')).toBeVisible();
  });
});
