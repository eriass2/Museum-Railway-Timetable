import { test, expect } from '@playwright/test';

test.describe('Month calendar (static mount)', () => {
  test('renders grid and opens day panel on click', async ({ page }) => {
    await page.goto('/month');
    const root = page.locator('.mrt-month');
    await expect(root).toBeVisible();
    await expect(page.locator('.mrt-calendar-grid--month')).toBeVisible();
    await expect(page.locator('.mrt-calendar-grid--month .mrt-day-clickable')).toHaveCount(2);
    await expect(
      page.getByRole('gridcell', { name: '5', exact: true }),
    ).toHaveClass(/mrt-day-cell--inactive/);

    await page.locator('.mrt-calendar-grid--month .mrt-day-clickable').first().click();
    const panel = page.locator('.mrt-html-panel');
    await expect(panel).toBeVisible();
    await expect(panel).toContainText('Tidtabell');
    await expect(panel).toHaveAttribute('tabindex', '-1');
  });

  test('shows legend hints', async ({ page }) => {
    await page.goto('/month');
    await expect(page.locator('.mrt-legend__hint')).toHaveCount(1);
  });

  test('shows error when day AJAX fails', async ({ page }) => {
    await page.goto('/month?fail=ajax');
    await page.locator('.mrt-day-clickable').first().click();
    await expect(page.locator('.mrt-ui-alert--error')).toBeVisible();
  });
});
