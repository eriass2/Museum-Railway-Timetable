import { test, expect } from '@playwright/test';
import { adminNavLink } from './admin-helpers';

const adminUrl = '/admin?page=mrt_app';

async function mountAdmin(page: import('@playwright/test').Page) {
  await page.goto(adminUrl);
  await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
}

test.describe('Admin settings page (static mount)', () => {
  test('saves operator name and keeps value after reload', async ({ page }) => {
    await mountAdmin(page);
    await adminNavLink(page, 'Inställningar').click();
    await expect(page).toHaveURL(/#\/settings/);
    await expect(page.getByRole('heading', { name: /^inställningar$/i })).toBeVisible();

    const operatorInput = page.locator('input.regular-text').first();
    await operatorInput.fill('E2E Operatör');
    await expect(page.locator('.mrt-admin-unsaved')).toBeVisible();
    await page.getByRole('button', { name: /spara inställningar/i }).click();
    await expect(page.getByText('Sparat.')).toBeVisible({ timeout: 10_000 });

    await page.reload();
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await adminNavLink(page, 'Inställningar').click();
    await expect(operatorInput).toHaveValue('E2E Operatör', { timeout: 10_000 });
  });
});
