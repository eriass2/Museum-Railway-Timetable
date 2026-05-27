import { test, expect } from '@playwright/test';

test.describe('Journey wizard (static mount)', () => {
  test('renders route step and step navigation', async ({ page }) => {
    await page.goto('/wizard');
    const root = page.locator('.mrt-journey-wizard');
    await expect(root).toBeVisible();
    await expect(root).toHaveAttribute('data-step', 'route');
    await expect(page.locator('.mrt-journey-wizard__nav')).toBeVisible();
    await expect(page.getByRole('heading', { name: /Sök din resa/i })).toBeVisible();
    await expect(page.locator('#mrt_wizard_from')).toBeVisible();
    await expect(page.locator('#mrt_wizard_to')).toBeVisible();
  });
});
