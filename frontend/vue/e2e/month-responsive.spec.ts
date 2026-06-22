import { test, expect } from '@playwright/test';

const OVERFLOW_SLACK = 2;
const clickableDay = '.mrt-calendar-grid--month .mrt-month-day--clickable';

async function expectNoHorizontalOverflow(page: import('@playwright/test').Page): Promise<void> {
  const overflow = await page.evaluate(() => {
    const doc = document.documentElement;
    return doc.scrollWidth - doc.clientWidth;
  });
  expect(overflow).toBeLessThanOrEqual(OVERFLOW_SLACK);
}

test.describe('Month calendar responsive layout', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/month');
    await expect(page.locator('.mrt-month')).toBeVisible();
  });

  test('has no page-level horizontal overflow at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await expectNoHorizontalOverflow(page);
  });

  test('has no page-level horizontal overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await expectNoHorizontalOverflow(page);
  });

  test('month table does not scroll horizontally at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    const table = page.locator('.mrt-month-table').first();
    await expect(table).toBeVisible();

    const metrics = await table.evaluate((el) => ({
      scrollWidth: el.scrollWidth,
      clientWidth: el.clientWidth,
    }));

    expect(metrics.scrollWidth).toBeLessThanOrEqual(metrics.clientWidth + OVERFLOW_SLACK);
  });

  test('nav controls meet touch target size at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    const next = page.locator('.mrt-calendar-nav__next').first();
    await expect(next).toBeVisible();

    const box = await next.boundingBox();
    expect(box).not.toBeNull();
    if (box) {
      expect(box.width).toBeGreaterThanOrEqual(42);
      expect(box.height).toBeGreaterThanOrEqual(42);
    }
  });

  test('day panel does not cause page overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.locator(clickableDay).first().click();
    await expect(page.locator('.mrt-html-panel')).toBeVisible();
    await expectNoHorizontalOverflow(page);
  });

  test('calendar respects app shell cap on wide desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 900 });
    const monthWidth = await page.locator('.mrt-month').evaluate((el) => el.getBoundingClientRect().width);

    const expectedMax = await page.evaluate(() => {
      const root = getComputedStyle(document.documentElement);
      const maxRem = parseFloat(root.getPropertyValue('--mrt-max-app')) * 16;
      const viewport = document.documentElement.clientWidth;
      return Math.min(viewport * 0.96, maxRem);
    });

    expect(monthWidth).toBeLessThanOrEqual(expectedMax + OVERFLOW_SLACK);
  });
});
