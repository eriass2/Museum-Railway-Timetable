import { test, expect } from '@playwright/test';

test.describe('Cancelled trips (static mount)', () => {
  test('overview shows cancelled rail column, branch row, and print key', async ({ page }) => {
    await page.goto('/overview-cancelled');
    await expect(page.locator('.mrt-vue-overview .mrt-ov')).toBeVisible();

    const badges = page.locator('.mrt-ov-cancelled-badge');
    await expect(badges.first()).toContainText('Inställd');
    await expect(badges).toHaveCount(3);

    await expect(page.locator('.mrt-ov-time--cancelled').first()).toBeVisible();
    await expect(page.locator('.mrt-ov-branch-row--cancelled')).toHaveCount(1);

    await expect(page.locator('.mrt-ov-print-key tbody tr').first()).toContainText('Inställd');
    await expect(page.locator('.mrt-ov-print-key tbody tr').nth(1)).toContainText('72 (Inställd)');
  });

  test('wizard outbound shows cancelled trip and disables select', async ({ page }) => {
    await page.goto('/wizard?debug=cancelled');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'outbound');

    const card = page.locator('.mrt-trip-list .mrt-trip-card').first();
    await expect(card.locator('.mrt-trip-summary__notice')).toContainText('Inställd');
    await expect(card.locator('.mrt-trip-summary__time--cancelled')).toBeVisible();
    await expect(card.getByRole('button', { name: /Välj/i })).toBeDisabled();
  });

  test('wizard detail timeline shows cancelled styling after expand', async ({ page }) => {
    await page.goto('/wizard?debug=cancelled');
    const card = page.locator('.mrt-trip-list .mrt-trip-card').first();
    await card.locator('.mrt-expand-trigger').click();
    await expect(page.locator('.mrt-detail-segment__notice--cancelled')).toContainText('Inställd');
    await expect(page.locator('.mrt-timeline__row--cancelled .mrt-timeline__time').first()).toBeVisible();
  });
});
