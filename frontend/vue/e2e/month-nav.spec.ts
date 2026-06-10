import { test, expect } from '@playwright/test';

const clickableDay = '.mrt-calendar-grid--month .mrt-month-day--clickable';

test.describe('Month calendar SPA navigation', () => {
  test('next month loads via REST without full page reload', async ({ page }) => {
    await page.addInitScript(() => {
      window.addEventListener('load', () => {
        (window as Window & { __mrtLoadCount?: number }).__mrtLoadCount =
          ((window as Window & { __mrtLoadCount?: number }).__mrtLoadCount || 0) + 1;
      });
    });

    await page.goto('/month');
    await expect(page.locator('.mrt-calendar-nav')).toBeVisible();
    await expect(page.locator('.mrt-calendar-nav__title')).toContainText('maj 2026');
    await expect(page.locator(clickableDay)).toHaveCount(2);

    await page.locator('.mrt-calendar-nav__next').click();
    await expect(page.locator('.mrt-calendar-nav__title')).toContainText('juni 2026', {
      timeout: 10_000,
    });
    await expect(page.locator(clickableDay)).toHaveCount(1);

    const loadCount = await page.evaluate(
      () => (window as Window & { __mrtLoadCount?: number }).__mrtLoadCount || 0,
    );
    expect(loadCount).toBe(1);

    await expect
      .poll(() => page.evaluate(() => window.location.search))
      .toContain('mrt_month=2026-06');
  });
});
