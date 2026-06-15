import { test, expect } from '@playwright/test';

/**
 * Visual regression for UL-style traffic info feed (TF-F4).
 * Snapshots: e2e/traffic-notices-ul-layout.spec.ts-snapshots/
 */
test.describe('Traffic notices UL layout', () => {
  async function openExpandedFeed(page: import('@playwright/test').Page) {
    await page.goto('/traffic-notices');
    const feed = page.locator('.mrt-tf-feed');
    await expect(feed).toBeVisible();
    const rows = feed.locator('.mrt-tf-category__row');
    const count = await rows.count();
    for (let i = 0; i < count; i++) {
      await rows.nth(i).click();
    }
    return feed;
  }

  test('desktop feed matches snapshot', async ({ page }) => {
    await page.setViewportSize({ width: 900, height: 900 });
    const feed = await openExpandedFeed(page);
    await expect(feed).toHaveScreenshot('traffic-notices-ul-desktop.png', {
      maxDiffPixelRatio: 0.02,
    });
  });

  test('mobile feed matches snapshot', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    const feed = await openExpandedFeed(page);
    await expect(feed).toHaveScreenshot('traffic-notices-ul-mobile.png', {
      maxDiffPixelRatio: 0.02,
    });
  });
});
