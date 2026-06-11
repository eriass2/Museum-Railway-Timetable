import { test, expect } from '@playwright/test';

test.describe('Traffic notices (static mount)', () => {
  test('lists ongoing feed items from REST', async ({ page }) => {
    await page.goto('/traffic-notices');
    await expect(page.locator('.mrt-traffic-notices')).toBeVisible();
    await expect(page.locator('.mrt-traffic-notices__section-title').first()).toHaveText('Pågår nu');
    await expect(page.locator('.mrt-traffic-notices__headline').first()).toContainText('Glassrean');
    await expect(page.locator('.mrt-traffic-notices__feed-item--cancelled')).toContainText('Inställd');
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
