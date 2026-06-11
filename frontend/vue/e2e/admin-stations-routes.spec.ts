import { test, expect } from '@playwright/test';
import { gotoAdminRoute } from './admin-helpers';

const adminUrl = '/admin?page=mrt_app';

test.describe('Stations and routes admin (static mount)', () => {
  test('shows train-change column hint and duplicate validation', async ({ page }) => {
    await gotoAdminRoute(page, adminUrl, '/stations-routes');
    await expect(page.getByRole('heading', { name: /stationer & rutter/i })).toBeVisible();

    await page.getByRole('button', { name: /redigera/i }).first().click();
    await page.getByText('Tågbyte').click();
    await expect(page.getByText(/Continuation-turer visas inte som egna kolumner/i)).toBeVisible();

    await page.getByRole('button', { name: /lägg till rad/i }).click();
    await page.locator('#tc-from-1-1').fill('72');
    await page.locator('#tc-type-1-1').fill('Dieseltåg');
    await page.locator('#tc-to-1-1').fill('61');

    await expect(page.getByText(/Samma continuation-tur används flera gånger/i)).toBeVisible();
  });
});
