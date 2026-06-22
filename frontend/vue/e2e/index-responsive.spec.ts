import { test, expect } from '@playwright/test';

const OVERFLOW_SLACK = 2;

async function expectNoHorizontalOverflow(page: import('@playwright/test').Page): Promise<void> {
  const overflow = await page.evaluate(() => {
    const doc = document.documentElement;
    return doc.scrollWidth - doc.clientWidth;
  });
  expect(overflow).toBeLessThanOrEqual(OVERFLOW_SLACK);
}

test.describe('Timetable index responsive layout', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/index');
    await expect(page.locator('.mrt-timetable-index')).toBeVisible();
  });

  test('has no page-level horizontal overflow at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await expectNoHorizontalOverflow(page);
  });

  test('has no page-level horizontal overflow at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await expectNoHorizontalOverflow(page);
  });

  test('index cards meet touch target height at 390px', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    const card = page.locator('.mrt-timetable-index__card').first();
    await expect(card).toBeVisible();

    const box = await card.boundingBox();
    expect(box).not.toBeNull();
    if (box) {
      expect(box.height).toBeGreaterThanOrEqual(44);
    }
  });

  test('long titles wrap without overflow at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    const title = page.locator('.mrt-timetable-index__title').first();
    await expect(title).toBeVisible();

    const metrics = await title.evaluate((el) => {
      const style = getComputedStyle(el);
      return {
        scrollWidth: el.scrollWidth,
        clientWidth: el.clientWidth,
        overflowWrap: style.overflowWrap,
      };
    });

    expect(metrics.scrollWidth).toBeLessThanOrEqual(metrics.clientWidth + OVERFLOW_SLACK);
    expect(['break-word', 'anywhere'].includes(metrics.overflowWrap) || metrics.scrollWidth <= metrics.clientWidth).toBe(true);
  });

  test('index respects content max-width token on wide desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 900 });
    const indexWidth = await page.locator('.mrt-timetable-index').evaluate((el) => {
      return el.getBoundingClientRect().width;
    });

    const maxContentPx = await page.evaluate(() => {
      return parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--mrt-max-content')) * 16;
    });

    expect(indexWidth).toBeLessThanOrEqual(maxContentPx + OVERFLOW_SLACK);
    expect(indexWidth).toBeGreaterThan(600);
  });
});
