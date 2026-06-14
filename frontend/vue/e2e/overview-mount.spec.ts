import { test, expect } from '@playwright/test';

test.describe('Timetable overview (static mount)', () => {
  test('loads overview from JSON AJAX with inline bus rows at junction', async ({ page }) => {
    await page.goto('/overview');
    await expect(page.locator('.mrt-vue-overview .mrt-ov')).toBeVisible();
    await expect(page.locator('.mrt-ov-banner')).toContainText('GRÖN TIDTABELL');
    await expect(page.locator('.mrt-ov-route-title').first()).toContainText('Uppsala Östra – Faringe');
    const tripHeads = page.locator('.mrt-ov-col-head--number');
    await expect(tripHeads).toHaveCount(6);
    const firstHead = await tripHeads.nth(0).boundingBox();
    const secondHead = await tripHeads.nth(1).boundingBox();
    expect(firstHead).not.toBeNull();
    expect(secondHead).not.toBeNull();
    expect(firstHead!.x).toBeLessThan(secondHead!.x);
    await expect(page.locator('.mrt-ov-grid-row--bus .mrt-ov-station-col').first()).toContainText(
      'Från Selknä*',
    );
    await expect(page.locator('.mrt-ov-bus-station-icon')).toHaveCount(2);
    await expect(page.locator('.mrt-ov-group--branch')).toHaveCount(0);
    await expect(page.locator('.mrt-ov-print-key-title')).toContainText('Förklaringar');
  });
});
