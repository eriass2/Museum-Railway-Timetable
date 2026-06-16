import { expect, type Locator, type Page } from '@playwright/test';

/** Wait until the Vue traffic feed has loaded REST data and rendered panels. */
export async function waitForTrafficFeedReady(feed: Locator): Promise<void> {
  await expect(feed).toBeVisible({ timeout: 20_000 });
  await expect(feed.locator('.mrt-traffic-notices__loading')).toHaveCount(0, { timeout: 20_000 });
  await expect(feed.locator('.mrt-tf-panel').first()).toBeVisible({ timeout: 20_000 });
}

export function ongoingTrafficPanel(page: Page) {
  return page.getByRole('region', { name: /aktuellt trafikläge/i });
}

export async function expandOngoingInformationCategory(page: Page): Promise<void> {
  const panel = ongoingTrafficPanel(page);
  await expect(panel).toBeVisible({ timeout: 15_000 });
  const infoRow = panel.getByRole('button', { name: /^information\b/i });
  await expect(infoRow).toBeVisible({ timeout: 10_000 });
  await infoRow.click();
  await expect(panel.locator('.mrt-tf-category.is-expanded')).toBeVisible();
}

/**
 * Click every category row in DOM order (accordion: last row per panel stays open).
 * Used by visual-regression specs that need a stable expanded state.
 */
export async function expandAllTrafficFeedCategories(feed: Locator): Promise<void> {
  await waitForTrafficFeedReady(feed);
  const rows = feed.locator('.mrt-tf-category__row');
  const count = await rows.count();
  for (let i = 0; i < count; i++) {
    await rows.nth(i).click();
  }
}
