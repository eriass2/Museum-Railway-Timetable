import { test, expect } from '@playwright/test';
import { gotoAdminRoute } from './admin-helpers';
import { wpDemoUrl, wpIndexUrl } from './wp-demo-url';
import { loginWpAdmin } from './wp-admin-login';

const adminUrl =
  process.env.MRT_E2E_WP_ADMIN_URL ||
  (wpDemoUrl
    ? `${wpDemoUrl.match(/^(https?:\/\/[^/]+)/)?.[1] || ''}/wp-admin/admin.php?page=mrt_app`
    : '');

async function openTrafficNotices(page: import('@playwright/test').Page): Promise<void> {
  await gotoAdminRoute(page, adminUrl, '/traffic-notices');
  await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
  await expect(page.getByRole('heading', { name: /^trafikmeddelanden$/i })).toBeVisible({
    timeout: 15_000,
  });
}

async function createNotice(
  page: import('@playwright/test').Page,
  text: string,
  options: { activeFrom?: string } = {},
): Promise<void> {
  await page.getByRole('button', { name: 'Nytt meddelande' }).click();
  await page.locator('textarea').fill(text);
  if (options.activeFrom) {
    await page.locator('input[type="date"]').first().fill(options.activeFrom);
  }
  await page.getByRole('button', { name: 'Spara' }).click();
  await expect(page.getByText('Meddelanden sparade')).toBeVisible({ timeout: 15_000 });
  await expect(page.locator('.widefat.striped tbody tr', { hasText: text })).toBeVisible({
    timeout: 10_000,
  });
}

test.describe('Vue admin traffic notices', () => {
  test.describe.configure({ mode: 'serial' });
  test.skip(!adminUrl, 'Set MRT_E2E_WP_ADMIN_URL or MRT_E2E_WP_DEMO_URL');

  test.beforeEach(async ({ page }) => {
    await loginWpAdmin(page);
  });

  test('creates a general notice and shows it on the index page', async ({ page }) => {
    test.skip(!wpIndexUrl, 'Set MRT_E2E_WP_INDEX_URL or sync timetable index page');
    const message = `E2E notice ${Date.now()}`;

    await openTrafficNotices(page);
    await createNotice(page, message);

    await page.goto(wpIndexUrl!);
    await expect(page.locator('.mrt-traffic-notices').first()).toBeVisible({
      timeout: 20_000,
    });
    await expect(
      page.locator('.mrt-tf-alert__summary', { hasText: message }),
    ).toBeVisible({ timeout: 15_000 });
  });

  test('reorders notices with up and down', async ({ page }) => {
    const first = `E2E reorder A ${Date.now()}`;
    const second = `E2E reorder B ${Date.now()}`;

    await openTrafficNotices(page);
    await createNotice(page, first);
    await createNotice(page, second);

    const rowFirst = page.locator('.widefat.striped tbody tr', { hasText: first });
    const rowSecond = page.locator('.widefat.striped tbody tr', { hasText: second });
    await expect(rowFirst).toBeVisible();
    await expect(rowSecond).toBeVisible();

    const rowIndex = (el: Element) =>
      Array.from(el.parentElement?.children ?? []).indexOf(el);

    const firstIndex = await rowFirst.evaluate(rowIndex);
    const secondIndex = await rowSecond.evaluate(rowIndex);
    expect(firstIndex).toBeLessThan(secondIndex);

    await rowSecond.getByRole('button', { name: 'Upp' }).click();
    await expect(page.getByText('Meddelanden sparade')).toBeVisible({ timeout: 15_000 });

    await expect
      .poll(async () => {
        const firstIndexAfter = await rowFirst.evaluate(rowIndex);
        const secondIndexAfter = await rowSecond.evaluate(rowIndex);
        return secondIndexAfter < firstIndexAfter;
      })
      .toBe(true);
  });

  test('shows hidden-today hint for future active_from date', async ({ page }) => {
    const message = `E2E future ${Date.now()}`;

    await openTrafficNotices(page);
    await page.getByRole('button', { name: 'Nytt meddelande' }).click();
    await page.locator('textarea').fill(message);
    await page.locator('input[type="date"]').first().fill('2099-12-01');
    await expect(page.getByText('Visas inte idag')).toBeVisible();
    await page.getByRole('button', { name: 'Spara' }).click();
    await expect(page.getByText('Meddelanden sparade')).toBeVisible({ timeout: 15_000 });
  });
});
