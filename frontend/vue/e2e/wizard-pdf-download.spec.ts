import { test, expect } from '@playwright/test';

test('PDF download triggers file', async ({ page }) => {
  const [download] = await Promise.all([
    page.waitForEvent('download', { timeout: 60000 }),
    page.goto('/wizard?debug=summary'),
    page.getByRole('button', { name: /Ladda ner som PDF/i }).click(),
  ]);
  expect(download.suggestedFilename()).toMatch(/\.pdf$/);
});
