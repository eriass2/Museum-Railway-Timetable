import { test, expect } from '@playwright/test';
import { gotoAdminRoute, useAdminMobileViewport } from './admin-helpers';

const OVERFLOW_SLACK = 2;
const adminUrl = '/admin?page=mrt_app';

async function expectNoHorizontalOverflow(page: import('@playwright/test').Page): Promise<void> {
  const overflow = await page.evaluate(() => {
    const doc = document.documentElement;
    return doc.scrollWidth - doc.clientWidth;
  });
  expect(overflow).toBeLessThanOrEqual(OVERFLOW_SLACK);
}

async function mountMobileEditor(page: import('@playwright/test').Page): Promise<void> {
  await useAdminMobileViewport(page);
  await gotoAdminRoute(page, adminUrl, '/timetables/1');
  await expect(page.locator('.mrt-admin-mobile-panel')).toBeVisible({ timeout: 15_000 });
  await expect(page.getByRole('heading', { name: /snabb avgångstid/i })).toBeVisible();
}

test.describe('Admin timetable editor mobile responsive layout', () => {
  test('mobile editor has no page-level horizontal overflow', async ({ page }) => {
    await mountMobileEditor(page);
    await expectNoHorizontalOverflow(page);
  });

  test('mobile quick departure form fields span full width', async ({ page }) => {
    await mountMobileEditor(page);
    const select = page.locator('#mrt-mobile-service');
    await expect(select).toBeVisible();

    const metrics = await select.evaluate((el) => {
      const field = el.closest('p');
      const panel = document.querySelector('.mrt-admin-mobile-panel');
      if (!field || !panel) {
        return { fieldWidth: 0, panelWidth: 0 };
      }
      return {
        fieldWidth: field.getBoundingClientRect().width,
        panelWidth: panel.getBoundingClientRect().width,
      };
    });

    expect(metrics.fieldWidth).toBeGreaterThan(metrics.panelWidth * 0.85);
  });

  test('mobile save departure button is full width', async ({ page }) => {
    await mountMobileEditor(page);
    await page.locator('#mrt-mobile-service').selectOption('101');
    const saveBtn = page.getByRole('button', { name: /spara avgångstid/i });
    await expect(saveBtn).toBeVisible();

    const metrics = await saveBtn.evaluate((el) => {
      const panel = el.closest('.mrt-admin-mobile-panel');
      if (!panel) {
        return { btnWidth: 0, panelWidth: 0 };
      }
      return {
        btnWidth: el.getBoundingClientRect().width,
        panelWidth: panel.getBoundingClientRect().width,
      };
    });

    expect(metrics.btnWidth).toBeGreaterThan(metrics.panelWidth * 0.9);
  });

  test('mobile deviation add form has no max-width cap', async ({ page }) => {
    await mountMobileEditor(page);
    const addForm = page.locator('.mrt-admin-mobile-deviation-add');
    await expect(addForm).toBeVisible();

    const maxWidth = await addForm.evaluate((el) => getComputedStyle(el).maxWidth);
    expect(maxWidth).toBe('none');
  });
});
