import { test, expect } from '@playwright/test';

test.describe('Journey wizard (static mount)', () => {
  test('renders route step and step navigation', async ({ page }) => {
    await page.goto('/wizard');
    const root = page.locator('.mrt-journey-wizard');
    await expect(root).toBeVisible();
    await expect(root).toHaveAttribute('data-step', 'route');
    await expect(page.locator('.mrt-step-nav')).toBeVisible();
    await expect(page.locator('.mrt-heading--surface-title')).toHaveText(/Planera resa/i);
    await expect(page.locator('#mrt_wizard_from')).toHaveAttribute('role', 'combobox');
    await expect(page.locator('#mrt_wizard_to')).toBeVisible();
    await expect(page.locator('.mrt-segmented')).toBeVisible();
  });

  test('submits feedback from widget', async ({ page }) => {
    await page.goto('/wizard?feedback=1');
    await page.getByRole('button', { name: /Rapportera fel eller förslag/i }).click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.getByLabel(/Beskrivning/i).fill('Datumsteget visar fel månad i kalendern.');
    await page.getByRole('button', { name: /^Skicka$/i }).click();
    await expect(page.getByText(/Tack! Vi har tagit emot din rapport/i)).toBeVisible();
  });
});
