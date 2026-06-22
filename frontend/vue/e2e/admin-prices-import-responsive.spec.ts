import { test, expect } from '@playwright/test';
import {
  adminNavLink,
  gotoAdminRoute,
  useAdminMobileViewport,
} from './admin-helpers';

const OVERFLOW_SLACK = 2;
const adminUrl = '/admin?page=mrt_app';

async function expectNoHorizontalOverflow(page: import('@playwright/test').Page): Promise<void> {
  const overflow = await page.evaluate(() => {
    const doc = document.documentElement;
    return doc.scrollWidth - doc.clientWidth;
  });
  expect(overflow).toBeLessThanOrEqual(OVERFLOW_SLACK);
}

async function mountMobileAdmin(page: import('@playwright/test').Page): Promise<void> {
  await useAdminMobileViewport(page);
  await page.goto(adminUrl);
  await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
}

test.describe('Admin prices, stations, and import mobile responsive layout', () => {
  test('price matrix scrolls inside table container on mobile', async ({ page }) => {
    await mountMobileAdmin(page);
    await adminNavLink(page, 'Priser').click();
    const scroll = page.locator('.admin-table-scroll:has(.mrt-price-matrix-table)');
    await expect(scroll).toBeVisible();

    const metrics = await scroll.evaluate((el) => ({
      overflowX: getComputedStyle(el).overflowX,
      scrollWidth: el.scrollWidth,
      clientWidth: el.clientWidth,
    }));

    expect(metrics.overflowX).toMatch(/auto|scroll/);
    expect(metrics.clientWidth).toBeLessThan(400);
    expect(metrics.scrollWidth).toBeGreaterThan(metrics.clientWidth);
  });

  test('prices page has no page-level horizontal overflow', async ({ page }) => {
    await mountMobileAdmin(page);
    await adminNavLink(page, 'Priser').click();
    await expect(page.getByRole('heading', { name: /^priser$/i })).toBeVisible();
    await expect(page.locator('.mrt-price-matrix-table')).toBeVisible();
    await expectNoHorizontalOverflow(page);
  });

  test('prices schema table stacks on mobile', async ({ page }) => {
    await mountMobileAdmin(page);
    await adminNavLink(page, 'Priser').click();
    const table = page.locator('.mrt-admin-prices-schema__table.mrt-admin-responsive-table').first();
    await expect(table).toBeVisible();

    const theadDisplay = await table.locator('thead').evaluate((el) => getComputedStyle(el).display);
    expect(theadDisplay).toBe('none');
  });

  test('prices inline form fields span full width on mobile', async ({ page }) => {
    await mountMobileAdmin(page);
    await adminNavLink(page, 'Priser').click();
    await page.locator('summary', { hasText: /prisstruktur/i }).click();
    const input = page.locator('.admin-inline-form input.regular-text').first();
    await expect(input).toBeVisible();

    const maxWidth = await input.evaluate((el) => getComputedStyle(el).maxWidth);
    expect(maxWidth).toBe('none');
  });

  test('stations page has no page-level horizontal overflow', async ({ page }) => {
    await mountMobileAdmin(page);
    await gotoAdminRoute(page, adminUrl, '/stations-routes', {
      heading: /stationer & rutter/i,
    });
    await expect(page.locator('.mrt-admin-stations-table')).toBeVisible();
    await expectNoHorizontalOverflow(page);
  });

  test('stations table stacks on mobile', async ({ page }) => {
    await mountMobileAdmin(page);
    await gotoAdminRoute(page, adminUrl, '/stations-routes', {
      heading: /stationer & rutter/i,
    });
    const table = page.locator('.mrt-admin-stations-table.mrt-admin-responsive-table');
    await expect(table).toBeVisible();

    const theadDisplay = await table.locator('thead').evaluate((el) => getComputedStyle(el).display);
    expect(theadDisplay).toBe('none');
  });

  test('import/export page has no page-level horizontal overflow', async ({ page }) => {
    await mountMobileAdmin(page);
    await gotoAdminRoute(page, adminUrl, '/import-export', {
      heading: /import\s*\/\s*export/i,
    });
    await expect(page.locator('.mrt-admin-import-file')).toBeAttached();
    await expectNoHorizontalOverflow(page);
  });

  test('import/export options stack vertically on mobile', async ({ page }) => {
    await mountMobileAdmin(page);
    await gotoAdminRoute(page, adminUrl, '/import-export', {
      heading: /import\s*\/\s*export/i,
    });
    const options = page.locator('.mrt-admin-import-export-options');
    await expect(options).toBeVisible();

    const flexDirection = await options.evaluate((el) => getComputedStyle(el).flexDirection);
    expect(flexDirection).toBe('column');
  });
});
