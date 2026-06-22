import { test, expect } from '@playwright/test';

const OVERFLOW_SLACK = 2;

async function expectNoHorizontalOverflow(page: import('@playwright/test').Page): Promise<void> {
  const overflow = await page.evaluate(() => {
    const doc = document.documentElement;
    return doc.scrollWidth - doc.clientWidth;
  });
  expect(overflow).toBeLessThanOrEqual(OVERFLOW_SLACK);
}

test.describe('Timetable overview responsive layout', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/overview');
    await expect(page.locator('.mrt-vue-overview .mrt-ov')).toBeVisible();
  });

  test('has no page-level horizontal overflow at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await expectNoHorizontalOverflow(page);
  });

  test('has no page-level horizontal overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await expectNoHorizontalOverflow(page);
  });

  test('grid scrolls inside container on narrow viewports', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    const scroll = page.locator('.mrt-ov-grid-scroll').first();
    await expect(scroll).toBeVisible();

    const metrics = await scroll.evaluate((el) => {
      const style = getComputedStyle(el);
      return {
        overflowX: style.overflowX,
        scrollWidth: el.scrollWidth,
        clientWidth: el.clientWidth,
      };
    });

    expect(metrics.overflowX).toMatch(/auto|scroll/);
    expect(metrics.scrollWidth).toBeGreaterThan(metrics.clientWidth);
  });

  test('overview uses full width cap on wide desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 900 });
    const overviewWidth = await page.locator('.mrt-ov').evaluate((el) => el.getBoundingClientRect().width);

    const expectedMax = await page.evaluate(() => {
      const root = getComputedStyle(document.documentElement);
      const maxRem = parseFloat(root.getPropertyValue('--mrt-max-app')) * 16;
      const viewport = document.documentElement.clientWidth;
      return Math.min(viewport * 0.96, maxRem);
    });

    expect(overviewWidth).toBeLessThanOrEqual(expectedMax + OVERFLOW_SLACK);
  });
});
