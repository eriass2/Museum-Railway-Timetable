import { test, expect } from '@playwright/test';

/**
 * Visual regression for wizard timeline (J22 line centering, J25 Ca stack, J26 info icon).
 * Snapshots live in e2e/wizard-timeline-layout.spec.ts-snapshots/.
 */
test.describe('Journey wizard timeline layout', () => {
  async function openExpandedTimeline(page: import('@playwright/test').Page) {
    await page.goto('/wizard?debug=outbound');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'outbound');
    const card = page.locator('.mrt-trip-list .mrt-trip-card').first();
    await card.locator('.mrt-expand-trigger').click();
    const timeline = page.locator('.mrt-timeline').first();
    await expect(timeline).toBeVisible({ timeout: 10_000 });
    await expect(timeline.locator('.mrt-timeline__node-col')).toHaveCount(3);
    await expect(timeline.locator('.mrt-timeline__node-col--segment-down')).toHaveCount(1);
    await expect(timeline.locator('.mrt-timeline__node-col--segment-up')).toHaveCount(1);
    await expect(timeline.locator('.mrt-timeline__node-col--segment-through')).toHaveCount(1);
    await expect(timeline.locator('.mrt-timeline__time-ca')).toHaveCount(0);
    await expect(timeline.locator('.mrt-timeline__info')).toHaveCount(0);
    return timeline;
  }

  test('desktop timeline matches snapshot', async ({ page }) => {
    await page.setViewportSize({ width: 900, height: 720 });
    const timeline = await openExpandedTimeline(page);
    await expect(timeline).toHaveScreenshot('wizard-timeline-desktop.png', {
      maxDiffPixelRatio: 0.02,
    });
  });

  test('mobile timeline matches snapshot', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 720 });
    const timeline = await openExpandedTimeline(page);
    await expect(timeline).toHaveScreenshot('wizard-timeline-mobile.png', {
      maxDiffPixelRatio: 0.02,
    });
  });

  test('expanded timeline shows middle stops and behov icon', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.goto('/wizard?debug=outbound');
    const card = page.locator('.mrt-trip-list .mrt-trip-card').first();
    await card.locator('.mrt-expand-trigger').click();
    const timeline = page.locator('.mrt-timeline').first();
    await expect(timeline).toBeVisible();

    await timeline.getByRole('button', { name: /Visa passerade/i }).click();
    await expect(timeline.locator('.mrt-timeline__station', { hasText: 'Lövstahagen' })).toBeVisible();
    await expect(timeline.locator('.mrt-timeline__time-ca')).toHaveCount(1);
    await expect(timeline.locator('.mrt-timeline__info')).toHaveCount(1);
    await expect(timeline.locator('.mrt-timeline__node-col--segment-down')).toHaveCount(1);
    await expect(timeline.locator('.mrt-timeline__node-col--segment-up')).toHaveCount(1);
    await expect(timeline.locator('.mrt-timeline__node-col--segment-through')).toHaveCount(4);

    const nodeCol = timeline.locator('.mrt-timeline__node-col').first();
    const alignment = await nodeCol.evaluate((col) => {
      const node = col.querySelector('.mrt-timeline__node');
      if (!node) {
        return 999;
      }
      const colRect = col.getBoundingClientRect();
      const nodeRect = node.getBoundingClientRect();
      const lineCenter = colRect.left + colRect.width / 2;
      const nodeCenter = nodeRect.left + nodeRect.width / 2;
      return Math.abs(lineCenter - nodeCenter);
    });
    expect(alignment).toBeLessThan(2);

    await expect(timeline).toHaveScreenshot('wizard-timeline-expanded-mobile.png', {
      maxDiffPixelRatio: 0.02,
    });
  });
});
