import { test, expect } from '@playwright/test';

/** Horizontal slack for sub-pixel rounding and scrollbar gutter. */
const OVERFLOW_SLACK = 2;

async function expectNoHorizontalOverflow(page: import('@playwright/test').Page): Promise<void> {
  const overflow = await page.evaluate(() => {
    const doc = document.documentElement;
    return doc.scrollWidth - doc.clientWidth;
  });
  expect(overflow).toBeLessThanOrEqual(OVERFLOW_SLACK);
}

test.describe('Journey wizard responsive layout', () => {
  test('route step has no horizontal overflow at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await page.goto('/wizard');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'route');
    await expectNoHorizontalOverflow(page);
  });

  test('route step has no horizontal overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.goto('/wizard');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'route');
    await expectNoHorizontalOverflow(page);
  });

  test('route step uses full viewport width at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.goto('/wizard');
    const hero = page.locator('.mrt-journey-wizard__hero').first();
    await expect(hero).toBeVisible();

    const metrics = await page.evaluate(() => {
      const root = document.querySelector('.mrt-journey-wizard');
      const rect = root?.getBoundingClientRect();
      const viewport = document.documentElement.clientWidth;
      return { left: rect?.left ?? 0, width: rect?.width ?? 0, viewport };
    });

    expect(metrics.left).toBeLessThanOrEqual(OVERFLOW_SLACK);
    expect(metrics.width).toBeGreaterThanOrEqual(metrics.viewport * 0.98 - OVERFLOW_SLACK);
  });

  test('outbound step has no horizontal overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.goto('/wizard?debug=outbound');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'outbound');
    await expectNoHorizontalOverflow(page);
  });

  test('summary step has no horizontal overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.goto('/wizard?debug=summary');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'summary');
    await expectNoHorizontalOverflow(page);
  });

  test('wizard content is capped on wide desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 900 });
    await page.goto('/wizard');
    await expect(page.locator('.mrt-journey-wizard')).toBeVisible();

    const metrics = await page.evaluate(() => {
      const root = getComputedStyle(document.documentElement);
      const maxRem = parseFloat(root.getPropertyValue('--mrt-max-wizard'));
      const viewport = document.documentElement.clientWidth;
      const expectedMax = Math.min(viewport * 0.54, maxRem * 16);
      const shell = document.querySelector('.mrt-journey-wizard .mrt-app-shell__content');
      return {
        contentWidth: shell?.getBoundingClientRect().width ?? 0,
        expectedMax,
      };
    });

    expect(metrics.contentWidth).toBeLessThanOrEqual(metrics.expectedMax + OVERFLOW_SLACK);
    expect(metrics.contentWidth).toBeGreaterThan(640);
  });
});
