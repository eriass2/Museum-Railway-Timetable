import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { DASHBOARD_HEADING } from './admin-helpers';
import { waitForTrafficFeedReady } from './traffic-feed-helpers';

const publicMounts = [
  { name: 'wizard route step', path: '/wizard' },
  { name: 'wizard date step', path: '/wizard?debug=date' },
  { name: 'month calendar', path: '/month' },
  { name: 'timetable overview', path: '/overview' },
  { name: 'traffic notices', path: '/traffic-notices' },
] as const;

const adminMounts = [{ name: 'admin dashboard', path: '/admin?page=mrt_app' }] as const;

for (const mount of publicMounts) {
  test(`a11y smoke: ${mount.name}`, async ({ page }) => {
    await page.goto(mount.path);
    if (mount.path === '/traffic-notices') {
      await waitForTrafficFeedReady(page.locator('.mrt-traffic-notices'));
    }
    const results = await new AxeBuilder({ page })
      .exclude('[aria-hidden="true"]')
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
      .analyze();

    expect(
      results.violations,
      formatViolations(mount.path, results.violations),
    ).toEqual([]);
  });
}

for (const mount of adminMounts) {
  test(`a11y smoke: ${mount.name}`, async ({ page }) => {
    await page.goto(mount.path);
    await expect(page.locator('#mrt-admin-app')).toBeVisible({ timeout: 20_000 });
    await expect(page.getByRole('heading', { name: DASHBOARD_HEADING })).toBeVisible({
      timeout: 15_000,
    });

    const results = await new AxeBuilder({ page })
      .exclude('[aria-hidden="true"]')
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
      .analyze();

    expect(
      results.violations,
      formatViolations(mount.path, results.violations),
    ).toEqual([]);
  });
}

function formatViolations(
  path: string,
  violations: { id: string; impact?: string | null; description: string; nodes: { target: string[] }[] }[],
): string {
  if (violations.length === 0) {
    return '';
  }

  const lines = violations.map((v) => {
    const targets = v.nodes.map((n) => n.target.join(' ')).join('; ');
    return `[${v.impact ?? 'unknown'}] ${v.id}: ${v.description} (${targets})`;
  });

  return `${path}\n${lines.join('\n')}`;
}
