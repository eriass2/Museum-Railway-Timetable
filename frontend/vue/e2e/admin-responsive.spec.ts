import { test, expect } from '@playwright/test';
import {
  adminNavLink,
  useAdminMobileViewport,
  DASHBOARD_HEADING,
  ADMIN_MOBILE_VIEWPORT,
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

async function mountMobileDashboard(page: import('@playwright/test').Page): Promise<void> {
  await useAdminMobileViewport(page);
  await page.goto(adminUrl);
  await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
  await expect(page.getByRole('heading', { name: DASHBOARD_HEADING })).toBeVisible({
    timeout: 15_000,
  });
  await expect(page.locator('.mrt-admin-stat-grid')).toBeVisible({ timeout: 15_000 });
}

test.describe('Admin shell and dashboard responsive layout', () => {
  test('mobile dashboard has no page-level horizontal overflow', async ({ page }) => {
    await mountMobileDashboard(page);
    await expectNoHorizontalOverflow(page);
  });

  test('mobile shell stacks nav above main content', async ({ page }) => {
    await mountMobileDashboard(page);
    const layout = await page.evaluate(() => {
      const shell = document.querySelector('.mrt-admin-shell');
      if (!shell) {
        return { flexDirection: '' };
      }
      return { flexDirection: getComputedStyle(shell).flexDirection };
    });
    expect(layout.flexDirection).toBe('column');
  });

  test('mobile nav horizontal scroll stays inside nav menu', async ({ page }) => {
    await mountMobileDashboard(page);
    await expectNoHorizontalOverflow(page);

    const menu = page.locator('.mrt-admin-shell__menu');
    await expect(menu).toBeVisible();
    const metrics = await menu.evaluate((el) => ({
      overflowX: getComputedStyle(el).overflowX,
      scrollWidth: el.scrollWidth,
      clientWidth: el.clientWidth,
    }));

    expect(metrics.overflowX).toMatch(/auto|scroll/);
    if (metrics.scrollWidth > metrics.clientWidth) {
      expect(metrics.clientWidth).toBeLessThan(ADMIN_MOBILE_VIEWPORT.width);
    }
  });

  test('mobile nav links meet touch target height', async ({ page }) => {
    await mountMobileDashboard(page);
    const link = adminNavLink(page, 'Tidtabeller');
    await expect(link).toBeVisible();

    const box = await link.boundingBox();
    expect(box).not.toBeNull();
    if (box) {
      expect(box.height).toBeGreaterThanOrEqual(44);
    }
  });

  test('desktop dashboard shows inline stats row', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto(adminUrl);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await expect(page.locator('.mrt-admin-stats')).toBeVisible({ timeout: 15_000 });
    await expect(page.locator('.mrt-admin-stat-grid')).toHaveCount(0);
    await expectNoHorizontalOverflow(page);
  });
});
