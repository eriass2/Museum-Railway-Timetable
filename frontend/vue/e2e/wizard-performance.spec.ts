import { test, expect } from '@playwright/test';

/** Generous CI budget — baseline only, not a hard perf gate. */
const DATE_STEP_MS = 3_000;
const OUTBOUND_STEP_MS = 3_000;

test.describe('Journey wizard performance baseline', () => {
  test('date step calendar visible within budget', async ({ page }) => {
    const start = Date.now();
    await page.goto('/wizard?debug=date');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'date');
    await expect(page.locator('[data-wizard-step="date"]')).toBeVisible();
    await expect(page.locator('.mrt-surface--flush')).toBeVisible();

    const elapsed = Date.now() - start;
    console.log(`wizard date step: ${elapsed}ms (budget ${DATE_STEP_MS}ms)`);
    expect(elapsed).toBeLessThan(DATE_STEP_MS);
  });

  test('outbound trip list visible within budget', async ({ page }) => {
    const start = Date.now();
    await page.goto('/wizard?debug=outbound');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'outbound');
    await expect(page.locator('.mrt-trip-list .mrt-trip-card')).toHaveCount(2);

    const elapsed = Date.now() - start;
    console.log(`wizard outbound step: ${elapsed}ms (budget ${OUTBOUND_STEP_MS}ms)`);
    expect(elapsed).toBeLessThan(OUTBOUND_STEP_MS);
  });
});
