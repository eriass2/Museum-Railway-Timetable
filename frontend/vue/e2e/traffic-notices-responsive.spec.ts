import { test, expect } from '@playwright/test';
import { expandAllTrafficFeedCategories, waitForTrafficFeedReady } from './traffic-feed-helpers';

const OVERFLOW_SLACK = 2;

async function expectNoHorizontalOverflow(page: import('@playwright/test').Page): Promise<void> {
  const overflow = await page.evaluate(() => {
    const doc = document.documentElement;
    return doc.scrollWidth - doc.clientWidth;
  });
  expect(overflow).toBeLessThanOrEqual(OVERFLOW_SLACK);
}

async function openExpandedFeed(page: import('@playwright/test').Page) {
  await page.goto('/traffic-notices');
  const feed = page.locator('.mrt-tf-feed');
  await expandAllTrafficFeedCategories(feed);
  return feed;
}

test.describe('Traffic notices responsive layout', () => {
  test('has no page-level horizontal overflow at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await openExpandedFeed(page);
    await expectNoHorizontalOverflow(page);
  });

  test('has no page-level horizontal overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await openExpandedFeed(page);
    await expectNoHorizontalOverflow(page);
  });

  test('feed respects max-width token on wide desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 900 });
    const feed = await openExpandedFeed(page);

    const metrics = await feed.evaluate((el) => {
      const root = getComputedStyle(document.documentElement);
      const maxFeedPx = parseFloat(root.getPropertyValue('--mrt-max-feed')) * 16;
      return {
        feedWidth: el.getBoundingClientRect().width,
        maxFeedPx,
      };
    });

    expect(metrics.feedWidth).toBeLessThanOrEqual(metrics.maxFeedPx + OVERFLOW_SLACK);
    expect(metrics.feedWidth).toBeGreaterThan(500);
  });

  test('category rows meet touch target height at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.goto('/traffic-notices');
    const feed = page.locator('.mrt-tf-feed');
    await waitForTrafficFeedReady(feed);

    const row = feed.locator('.mrt-tf-category__row').first();
    await expect(row).toBeVisible();
    const box = await row.boundingBox();
    expect(box).not.toBeNull();
    if (box) {
      expect(box.height).toBeGreaterThanOrEqual(44);
    }
  });

  test('feed stays within shell content at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    const feed = await openExpandedFeed(page);

    const metrics = await page.evaluate(() => {
      const feedEl = document.querySelector('.mrt-tf-feed');
      const shell = document.querySelector('.mrt-app-shell__content');
      if (!feedEl || !shell) {
        return { feedWidth: 0, shellWidth: 0 };
      }
      return {
        feedWidth: feedEl.getBoundingClientRect().width,
        shellWidth: shell.getBoundingClientRect().width,
      };
    });

    expect(metrics.feedWidth).toBeLessThanOrEqual(metrics.shellWidth + OVERFLOW_SLACK);
  });
});
