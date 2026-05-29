import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';

const wpUrl = wpDemoUrl || process.env.MRT_E2E_WP_URL;

test.describe('Journey wizard (WordPress)', () => {
  test.skip(!wpUrl, 'Set MRT_E2E_WP_DEMO_URL or MRT_E2E_WP_URL');

  test('demo page mounts wizard', async ({ page }) => {
    await page.goto(wpUrl!);
    const section = page.locator('h2', { hasText: /journey wizard|reseplanerare/i });
    if (await section.count()) {
      await section.first().scrollIntoViewIfNeeded();
    }
    const root = page.locator('.mrt-journey-wizard').first();
    await expect(root).toBeVisible({ timeout: 20_000 });
    await expect(root.locator('.mrt-step-nav')).toBeVisible();
  });
});
