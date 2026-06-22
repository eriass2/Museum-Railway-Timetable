import { test, expect } from '@playwright/test';

/**
 * Layout guardrails for wizard route step.
 * Target: stepper in main card shell; route title + form in white search panel.
 */
test.describe('Journey wizard route step layout', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 900, height: 820 });
    await page.goto('/wizard');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'route');
  });

  test('stepper and search panel live inside the wizard main card', async ({ page }) => {
    const mainCard = page.locator('.mrt-wizard-main-card').first();
    await expect(mainCard).toBeVisible();

    const mainCardBg = await mainCard.evaluate((el) => getComputedStyle(el).backgroundColor);
    expect(mainCardBg).not.toBe('rgba(0, 0, 0, 0)');

    const stepper = mainCard.locator('.mrt-step-nav').first();
    await expect(stepper).toBeVisible();

    const searchPanel = mainCard.locator('.mrt-step-panel--search').first();
    await expect(searchPanel).toBeVisible();
    const panelBg = await searchPanel.evaluate((el) => getComputedStyle(el).backgroundColor);
    expect(panelBg).toBe('rgb(255, 255, 255)');
  });

  test('route title sits in white search panel with dark text', async ({ page }) => {
    const searchPanel = page.locator('.mrt-step-panel--search').first();
    await expect(searchPanel).toBeVisible();

    const routeTitle = searchPanel.locator('.mrt-heading--surface-title').first();
    await expect(routeTitle).toBeVisible();
    const titleColor = await routeTitle.evaluate((el) => getComputedStyle(el).color);
    expect(titleColor).not.toBe('rgb(255, 255, 255)');
    expect(titleColor).not.toBe('rgba(0, 0, 0, 0)');
  });

  test('trip type control appears before stacked station fields', async ({ page }) => {
    const segmented = page.locator('.mrt-segmented').first();
    const fromField = page.locator('#mrt_wizard_from').first();
    await expect(segmented).toBeVisible();
    await expect(fromField).toBeVisible();

    const order = await page.evaluate(() => {
      const trip = document.querySelector('.mrt-segmented');
      const from = document.querySelector('#mrt_wizard_from');
      if (!trip || !from) {
        return { tripBeforeFrom: false };
      }
      return { tripBeforeFrom: (trip.compareDocumentPosition(from) & Node.DOCUMENT_POSITION_FOLLOWING) !== 0 };
    });
    expect(order.tripBeforeFrom).toBe(true);
  });

  test('station fields are stacked (single-column grid)', async ({ page }) => {
    const stations = page.locator('.mrt-route-layout__stations').first();
    await expect(stations).toBeVisible();

    const layout = await stations.evaluate((el) => {
      const style = getComputedStyle(el);
      const columnTracks = style.gridTemplateColumns.split(' ').filter((part) => part.trim() !== '');
      const fields = el.querySelectorAll('.mrt-journey-wizard__station-field');
      if (fields.length < 2) {
        return { columnTracks: columnTracks.length, stacked: false };
      }
      const first = fields[0].getBoundingClientRect();
      const second = fields[1].getBoundingClientRect();
      return {
        columnTracks: columnTracks.length,
        stacked: second.top >= first.bottom - 4,
      };
    });

    expect(layout.columnTracks).toBe(1);
    expect(layout.stacked).toBe(true);
  });

  test('compact segmented control sizing is applied', async ({ page }) => {
    const option = page.locator('.mrt-segmented__option').first();
    await expect(option).toBeVisible();
    const minHeight = await option.evaluate((el) => parseFloat(getComputedStyle(el).minHeight));
    expect(minHeight).toBeGreaterThanOrEqual(40);
    expect(minHeight).toBeLessThanOrEqual(52);
  });
});
