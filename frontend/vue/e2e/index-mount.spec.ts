import { test, expect } from '@playwright/test';

test.describe('Timetable index (static mount)', () => {
  test('renders intro, links, meta and color modifiers', async ({ page }) => {
    await page.goto('/index');
    const root = page.locator('.mrt-timetable-index');
    await expect(root).toBeVisible();
    await expect(root.locator('.mrt-timetable-index__intro')).toContainText(
      'Välj en tidtabell',
    );
    await expect(page.getByRole('navigation', { name: 'Tidtabeller' })).toBeVisible();

    const items = root.locator('.mrt-timetable-index__item');
    await expect(items).toHaveCount(3);
    await expect(items.nth(0).locator('.mrt-timetable-index__card')).toHaveClass(/mrt-timetable-index__card--green/);
    await expect(items.nth(1).locator('.mrt-timetable-index__card')).toHaveClass(/mrt-timetable-index__card--yellow/);
    await expect(items.nth(2).locator('.mrt-timetable-index__card')).toHaveClass(/mrt-timetable-index__card--red/);

    const greenLink = root.getByRole('link', { name: 'Grön tidtabell — 12 juni, 19 juni' });
    await expect(greenLink).toHaveAttribute('href', '/timetables/green');
    await expect(greenLink.locator('.mrt-timetable-index__meta')).toContainText('12 juni');

    await expect(root.locator('.mrt-timetable-index__card--static')).toHaveCount(1);
    await expect(root.locator('.mrt-timetable-index__card--static')).toContainText('Tidtabell utan sida');
  });

  test('hides intro when showIntro is false', async ({ page }) => {
    await page.goto('/index?intro=0');
    await expect(page.locator('.mrt-timetable-index')).toBeVisible();
    await expect(page.locator('.mrt-timetable-index__intro')).toHaveCount(0);
    await expect(page.locator('.mrt-timetable-index__item')).toHaveCount(3);
  });

  test('shows empty state message', async ({ page }) => {
    await page.goto('/index?empty=1');
    await expect(page.locator('.mrt-ui-alert')).toContainText('Inga tidtabeller');
    await expect(page.locator('.mrt-timetable-index')).toHaveCount(0);
  });
});
