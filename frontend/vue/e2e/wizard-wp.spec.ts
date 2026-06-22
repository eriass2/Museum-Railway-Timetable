import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';

test.describe('Journey wizard (WordPress)', () => {
  test.skip(!wpDemoUrl, 'Set MRT_E2E_WP_DEMO_URL');

  test('demo page mounts wizard', async ({ page }) => {
    await page.goto(wpDemoUrl);
    const section = page.locator('h2', { hasText: /journey wizard|reseplanerare/i });
    if (await section.count()) {
      await section.first().scrollIntoViewIfNeeded();
    }
    const root = page.locator('.mrt-journey-wizard').first();
    await expect(root).toBeVisible({ timeout: 20_000 });
    await expect(root).toHaveAttribute('data-step', 'route');
    await expect(root.locator('.mrt-step-nav')).toBeVisible();
    await expect(root.locator('.mrt-step-progress__item.is-active')).toBeVisible();
  });
});
