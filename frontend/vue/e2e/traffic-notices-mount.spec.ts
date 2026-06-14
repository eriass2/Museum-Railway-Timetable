import { test, expect } from '@playwright/test';

test.describe('Traffic notices (static mount)', () => {
  test('lists ongoing feed items from REST', async ({ page }) => {
    await page.goto('/traffic-notices');
    await expect(page.locator('.mrt-traffic-notices')).toBeVisible();
    await expect(page.locator('.mrt-traffic-notices__section-title').first()).toHaveText('Pågår nu');
    await expect(page.locator('.mrt-traffic-notices__headline').first()).toContainText('Glassrean');
    await expect(page.locator('.mrt-traffic-notices__feed-item--cancelled')).toContainText('Inställd');
    await page.locator('.mrt-traffic-notices__feed-item').first().locator('.mrt-expand-trigger').click();
    await expect(page.locator('.mrt-traffic-notices__intro').first()).toBeVisible();
  });

  test('accordion keeps one expanded item at a time', async ({ page }) => {
    await page.goto('/traffic-notices');
    const items = page.locator('.mrt-traffic-notices__feed-item');
    await items.nth(0).locator('.mrt-traffic-notices__summary-row--interactive').click();
    await expect(items.nth(0)).toHaveClass(/is-expanded/);
    await items.nth(1).locator('.mrt-traffic-notices__summary-row--interactive').click();
    await expect(items.nth(1)).toHaveClass(/is-expanded/);
    await expect(items.nth(0)).not.toHaveClass(/is-expanded/);
  });

  test('shows empty state', async ({ page }) => {
    await page.goto('/traffic-notices?empty=1');
    await expect(page.locator('.mrt-traffic-notices__empty')).toBeVisible();
    await expect(page.locator('.mrt-traffic-notices__empty')).toContainText('Inga meddelanden');
  });

  test('shows optional title', async ({ page }) => {
    await page.goto('/traffic-notices?title=Trafikinfo');
    await expect(page.locator('.mrt-traffic-notices__title')).toHaveText('Trafikinfo');
  });
});
