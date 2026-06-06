import { test, expect } from '@playwright/test';
import { adminNavLink, useAdminMobileViewport } from './admin-helpers';

const adminUrl = '/admin?page=mrt_app';

async function mountAdmin(page: import('@playwright/test').Page) {
  await useAdminMobileViewport(page);
  await page.goto(adminUrl);
  await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
}

test.describe('Admin prices page (static mount)', () => {
  test('shows preview and unsaved banner when matrix is edited', async ({ page }) => {
    await mountAdmin(page);
    await adminNavLink(page, 'Priser').click();
    await expect(page).toHaveURL(/#\/prices/);
    await expect(page.getByRole('heading', { name: /^priser$/i })).toBeVisible();

    await expect(page.locator('.mrt-admin-prices-preview')).toBeVisible();
    const priceInput = page.locator('.mrt-price-matrix-table input[type="number"]').first();
    await expect(priceInput).toBeVisible();
    await priceInput.fill('999');

    await expect(page.locator('.mrt-admin-unsaved')).toBeVisible();
  });

  test('prompts before leaving with unsaved price changes', async ({ page }) => {
    await mountAdmin(page);
    await adminNavLink(page, 'Priser').click();

    const priceInput = page.locator('.mrt-price-matrix-table input[type="number"]').first();
    await priceInput.fill('888');

    await adminNavLink(page, 'Översikt').click();
    await expect(page.getByRole('alertdialog')).toBeVisible();
    await page.getByRole('button', { name: /avbryt/i }).click();

    await expect(page).toHaveURL(/#\/prices/);
    await expect(priceInput).toHaveValue('888');
  });
});
