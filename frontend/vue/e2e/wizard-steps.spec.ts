import { test, expect } from '@playwright/test';

test.describe('Journey wizard steps', () => {
  test('date step via debug preset', async ({ page }) => {
    await page.goto('/wizard?debug=date');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'date');
    await expect(page.locator('[data-wizard-step="date"]')).toBeVisible();
    await expect(page.locator('.mrt-surface--flush')).toBeVisible();
  });

  test('outbound step lists trips and advances on select', async ({ page }) => {
    await page.goto('/wizard?debug=outbound');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'outbound');
    const list = page.locator('.mrt-trip-list');
    await expect(list).toBeVisible();
    await expect(list.locator('.mrt-trip-card')).toHaveCount(2);
    await list.getByRole('button', { name: /Välj/i }).first().click();
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'return');
  });

  test('return trip shows five progress steps', async ({ page }) => {
    await page.goto('/wizard?debug=return');
    await expect(page.locator('.mrt-step-progress__item')).toHaveCount(5);
    await expect(page.locator('.mrt-step-progress__item.is-active')).toContainText(/återresa/i);
  });

  test('completed progress steps navigate back', async ({ page }) => {
    await page.goto('/wizard?debug=summary');
    await page.getByRole('button', { name: /datum/i }).click();
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'date');
    await expect(page.locator('[data-wizard-step="date"]')).toBeVisible();
  });

  test('return step shows selected outbound banner', async ({ page }) => {
    await page.goto('/wizard?debug=return');
    await expect(page.locator('.mrt-selected-trip[data-wizard-return-summary]')).toBeVisible();
    await expect(page.locator('.mrt-selected-trip__label')).toContainText(/utresa/i);
    await page.locator('.mrt-trip-list').getByRole('button', { name: /Välj/i }).click();
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'summary');
  });

  test('summary step shows trip summary cards and price table', async ({ page }) => {
    await page.goto('/wizard?debug=summary');
    await expect(page.locator('.mrt-journey-wizard')).toHaveAttribute('data-step', 'summary');
    await expect(page.locator('.mrt-summary-list')).toBeVisible();
    await expect(page.locator('.mrt-summary-card')).toHaveCount(2);
    await expect(page.locator('.mrt-trip-summary')).toHaveCount(2);
    await expect(page.locator('.mrt-price-list').first()).toBeVisible({ timeout: 10_000 });
    await expect(page.locator('.mrt-price-list').first()).toContainText('180 kr');
  });

  test('summary step shows print and PDF actions, not ticket CTA', async ({ page }) => {
    await page.goto('/wizard?debug=summary');
    await expect(page.getByRole('button', { name: /Skriv ut/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /Ladda ner som PDF/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /Fortsätt till biljetter/i })).toHaveCount(0);
    await expect(page.getByText(/Fortsätt till biljetter/i)).toHaveCount(0);
  });
});
