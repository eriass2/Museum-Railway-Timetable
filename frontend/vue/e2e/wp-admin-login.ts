import type { Page } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';

function wpSiteBase(): string {
  const adminUrl = process.env.MRT_E2E_WP_ADMIN_URL || '';
  if (adminUrl) {
    const match = adminUrl.match(/^(https?:\/\/[^/]+)/);
    if (match?.[1]) {
      return match[1];
    }
  }
  if (wpDemoUrl) {
    const match = wpDemoUrl.match(/^(https?:\/\/[^/]+)/);
    if (match?.[1]) {
      return match[1];
    }
  }
  return process.env.MRT_E2E_WP_SITE_URL || process.env.MRT_DEV_SITE_URL || 'http://localhost:8080';
}

export async function loginWpAdmin(page: Page): Promise<void> {
  const user = process.env.MRT_E2E_WP_ADMIN_USER || 'admin';
  const pass = process.env.MRT_E2E_WP_ADMIN_PASSWORD || 'admin';
  const base = wpSiteBase();

  await page.goto(`${base}/wp-login.php`);
  await page.locator('#user_login').fill(user);
  await page.locator('#user_pass').fill(pass);
  await page.locator('#wp-submit').click();
  await page.waitForURL(/wp-admin/, { timeout: 20_000 });
}
