import { test, expect } from '@playwright/test';
import { waitForTrafficFeedReady } from './traffic-feed-helpers';

test.describe('Traffic notices (static mount)', () => {
  function ongoingPanel(page: import('@playwright/test').Page) {
    return page
      .locator('.mrt-tf-panel')
      .filter({ has: page.locator('.mrt-tf-panel__header', { hasText: 'Aktuellt trafikläge' }) });
  }

  test('lists ongoing feed items from REST', async ({ page }) => {
    await page.goto('/traffic-notices');
    const feed = page.locator('.mrt-traffic-notices');
    await waitForTrafficFeedReady(feed);
    const ongoing = ongoingPanel(page);
    await expect(ongoing.locator('.mrt-tf-panel__header')).toContainText('Aktuellt trafikläge');
    await ongoing.locator('.mrt-tf-category__row').filter({ hasText: 'Information' }).click();
    await expect(ongoing.locator('.mrt-tf-alert__summary').filter({ hasText: 'Glassrea' })).toBeVisible();
    await ongoing.locator('.mrt-tf-category__row').filter({ hasText: 'Tåg' }).click();
    await expect(ongoing.locator('.mrt-tf-line-badge').filter({ hasText: '71' })).toBeVisible();
    await expect(ongoing.locator('.mrt-tf-alert__summary').filter({ hasText: 'Inställd' })).toBeVisible();
    await ongoing.locator('.mrt-tf-alert__expand').first().click();
    await expect(ongoing.locator('.mrt-tf-alert__detail').first()).toBeVisible();
  });

  test('accordion keeps one expanded category at a time per panel', async ({ page }) => {
    await page.goto('/traffic-notices');
    await waitForTrafficFeedReady(page.locator('.mrt-traffic-notices'));
    const categories = ongoingPanel(page).locator('.mrt-tf-category');
    await categories.filter({ hasText: 'Information' }).locator('.mrt-tf-category__row').click();
    await expect(categories.filter({ hasText: 'Information' })).toHaveClass(/is-expanded/);
    await categories.filter({ hasText: 'Tåg' }).locator('.mrt-tf-category__row').click();
    await expect(categories.filter({ hasText: 'Tåg' })).toHaveClass(/is-expanded/);
    await expect(categories.filter({ hasText: 'Information' })).not.toHaveClass(/is-expanded/);
  });

  test('shows empty state', async ({ page }) => {
    await page.goto('/traffic-notices?empty=1');
    await expect(page.locator('.mrt-traffic-notices__empty')).toBeVisible();
    await expect(page.locator('.mrt-traffic-notices__empty')).toContainText('Inga meddelanden');
  });

  test('shows optional title', async ({ page }) => {
    await page.goto('/traffic-notices?title=Trafikinfo');
    await expect(page.locator('.mrt-traffic-notices__title')).toHaveText('Trafikinfo');
  });

  test('shows only upcoming panel when ongoing is empty (TF-C8)', async ({ page }) => {
    await page.goto('/traffic-notices?upcoming-only=1');
    await waitForTrafficFeedReady(page.locator('.mrt-traffic-notices'));
    await expect(page.locator('.mrt-tf-panel')).toHaveCount(1);
    await expect(page.locator('.mrt-tf-panel__header')).toHaveText('Planerade avvikelser');
    await expect(page.locator('.mrt-tf-panel__header')).not.toContainText('Aktuellt trafikläge');
    await page.locator('.mrt-tf-category__row').first().click();
    await expect(page.locator('.mrt-tf-alert__summary')).toContainText('Buss ersätter');
  });
});
