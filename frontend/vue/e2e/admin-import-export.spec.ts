import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { test, expect } from '@playwright/test';
import { gotoAdminRoute } from './admin-helpers';
import { wpDemoUrl } from './wp-demo-url';
import { loginWpAdmin } from './wp-admin-login';

const adminUrl =
  process.env.MRT_E2E_WP_ADMIN_URL ||
  (wpDemoUrl
    ? `${wpDemoUrl.match(/^(https?:\/\/[^/]+)/)?.[1] || ''}/wp-admin/admin.php?page=mrt_app`
    : '');

const fixtureZip = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  '../../../testdata/fixtures/lennakatten.zip',
);

test.describe('Vue admin import/export', () => {
  test.describe.configure({ mode: 'serial' });
  test.skip(!adminUrl, 'Set MRT_E2E_WP_ADMIN_URL or MRT_E2E_WP_DEMO_URL');

  test.beforeEach(async ({ page }) => {
    await loginWpAdmin(page);
  });

  test('imports Lennakatten fixture zip in merge mode', async ({ page }) => {
    await gotoAdminRoute(page, adminUrl, '/import-export', {
      heading: /import\s*\/\s*export/i,
    });

    const fileInput = page.locator('input.mrt-admin-import-file');
    await fileInput.setInputFiles(fixtureZip);
    await expect(page.getByText(/import klar/i)).toBeVisible({ timeout: 60_000 });
  });
});
