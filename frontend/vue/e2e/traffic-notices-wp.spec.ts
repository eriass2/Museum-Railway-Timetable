import { test, expect } from '@playwright/test';
import { wpDemoUrl } from './wp-demo-url';

test.describe('Traffic notices (WordPress)', () => {
  test.skip(!wpDemoUrl, 'Set MRT_E2E_WP_DEMO_URL');

  test('shortcode mounts on component demo page', async ({ page }) => {
    await page.goto(wpDemoUrl!);
    const section = page.locator('h2', { hasText: /trafikmeddelanden/i });
    await section.first().scrollIntoViewIfNeeded();
    await expect(page.locator('.mrt-traffic-notices').first()).toBeVisible({
      timeout: 20_000,
    });
  });
});
