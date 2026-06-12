import { test, expect } from '@playwright/test';

/**
 * Smoke layout checks for other public apps after Phase 3 CSS migration.
 */
test.describe('Public app chunk layout', () => {
  test('month calendar grid renders clickable days', async ({ page }) => {
    await page.goto('/month');
    await expect(page.locator('.mrt-month')).toBeVisible();
    await expect(page.locator('.mrt-calendar-grid--month')).toBeVisible();
    await expect(page.locator('.mrt-month-day--clickable')).not.toHaveCount(0);

    const dayMinHeight = await page.locator('.mrt-month-day').first().evaluate((el) => {
      return parseFloat(getComputedStyle(el).minHeight);
    });
    expect(dayMinHeight).toBeGreaterThan(40);
  });

  test('timetable index cards use grid layout', async ({ page }) => {
    await page.goto('/index');
    const card = page.locator('.mrt-timetable-index__card').first();
    await expect(card).toBeVisible();
    const display = await card.evaluate((el) => getComputedStyle(el).display);
    expect(display).toBe('grid');
  });

  test('traffic notices feed renders list items', async ({ page }) => {
    await page.goto('/traffic-notices');
    await expect(page.locator('.mrt-traffic-notices')).toBeVisible();
    await expect(page.locator('.mrt-traffic-notices__feed-item').first()).toBeVisible();
  });
});
