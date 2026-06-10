import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const publicMounts = [
  { name: 'wizard route step', path: '/wizard' },
  { name: 'wizard date step', path: '/wizard?debug=date' },
  { name: 'month calendar', path: '/month' },
  { name: 'timetable overview', path: '/overview' },
  { name: 'traffic notices', path: '/traffic-notices' },
] as const;

for (const mount of publicMounts) {
  test(`a11y smoke: ${mount.name}`, async ({ page }) => {
    await page.goto(mount.path);
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
