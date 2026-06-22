import { test, expect } from '@playwright/test';

test.describe('Journey wizard (static mount)', () => {
  test('renders route step and step navigation', async ({ page }) => {
    await page.goto('/wizard');
    const root = page.locator('.mrt-journey-wizard');
    await expect(root).toBeVisible();
    await expect(root).toHaveAttribute('data-step', 'route');
    await expect(page.locator('.mrt-step-nav')).toBeVisible();
    await expect(page.locator('.mrt-step-progress__item.is-active')).toContainText(/sök|resa|route/i);
    await expect(page.locator('.mrt-heading--surface-title')).toHaveText(/Planera resa/i);
    await expect(page.locator('#mrt_wizard_from')).toHaveAttribute('role', 'combobox');
    await expect(page.locator('#mrt_wizard_to')).toBeVisible();
    await expect(page.locator('.mrt-segmented')).toBeVisible();
  });

  test('step progress columns align on mobile grid', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 820 });
    await page.goto('/wizard?debug=date');
    const items = page.locator('.mrt-step-progress__item');
    await expect(items).toHaveCount(4);

    const columns = await page.evaluate(() => {
      const buttons = Array.from(document.querySelectorAll('.mrt-step-progress__item'));
      if (buttons.length < 4) {
        return { aligned: false };
      }
      const rects = buttons.map((el) => el.getBoundingClientRect());
      const leftDelta = Math.abs(rects[0].left - rects[2].left);
      const rightDelta = Math.abs(rects[1].right - rects[3].right);
      const col1WidthDelta = Math.abs(rects[0].width - rects[2].width);
      const col2WidthDelta = Math.abs(rects[1].width - rects[3].width);
      return {
        aligned: leftDelta < 2 && rightDelta < 2 && col1WidthDelta < 2 && col2WidthDelta < 2,
        leftDelta,
        rightDelta,
        col1WidthDelta,
        col2WidthDelta,
      };
    });

    expect(columns.aligned).toBe(true);
  });

  test('submits feedback from widget', async ({ page }) => {
    await page.goto('/wizard?feedback=1');
    await page.getByRole('button', { name: /Rapportera fel eller förslag/i }).click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.getByLabel(/Beskrivning/i).fill('Datumsteget visar fel månad i kalendern.');
    await page.getByRole('button', { name: /^Skicka$/i }).click();
    await expect(page.getByText(/Tack! Vi har tagit emot din rapport/i)).toBeVisible();
  });
});
