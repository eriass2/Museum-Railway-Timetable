import { test, expect } from '@playwright/test';

test.describe('Journey wizard summary PDF', () => {
  test('downloads PDF from server endpoint', async ({ page }) => {
    await page.goto('/wizard?debug=summary');
    const downloadPromise = page.waitForEvent('download');
    await page.getByRole('button', { name: /Ladda ner som PDF/i }).click();
    const download = await downloadPromise;
    expect(download.suggestedFilename()).toMatch(/\.pdf$/i);
  });

  test('shows server error when PDF endpoint fails', async ({ page }) => {
    await page.goto('/wizard?debug=summary&pdf=fail');
    await page.getByRole('button', { name: /Ladda ner som PDF/i }).click();
    await expect(page.getByRole('alert')).toContainText('PDF kunde inte skapas (e2e)');
  });
});
