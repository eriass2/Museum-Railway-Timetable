import { test, expect } from '@playwright/test';
import { wpIndexUrl } from './wp-demo-url';

test.describe('Journey wizard on Trafikkalender (WordPress front page)', () => {
  test.skip(!wpIndexUrl, 'Set MRT_E2E_WP_INDEX_URL or MRT_E2E_WP_DEMO_URL');

  test('stacked shortcodes mount wizard with hero background at full width', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto(wpIndexUrl);

    const wizardRoot = page.locator('.mrt-vue-root--wizard').first();
    await expect(wizardRoot).toBeVisible({ timeout: 20_000 });

    const wizard = page.locator('.mrt-journey-wizard').first();
    await expect(wizard).toBeVisible();
    await expect(wizard).not.toHaveClass(/mrt-journey-wizard--embedded/);

    const hero = wizard.locator('.mrt-journey-wizard__hero--has-bg').first();
    await hero.scrollIntoViewIfNeeded();
    await expect(hero).toBeVisible();

    const heroMetrics = await hero.evaluate((el) => {
      const style = getComputedStyle(el);
      const rect = el.getBoundingClientRect();
      return {
        backgroundImage: style.backgroundImage,
        width: rect.width,
        left: rect.left,
      };
    });

    expect(heroMetrics.backgroundImage).not.toBe('none');
    expect(heroMetrics.backgroundImage).toMatch(/url\(/);

    const viewport = page.viewportSize();
    expect(viewport).not.toBeNull();
    if (viewport) {
      expect(heroMetrics.width).toBeGreaterThan(viewport.width * 0.95);
      expect(heroMetrics.left).toBeLessThan(8);
    }
  });
});
