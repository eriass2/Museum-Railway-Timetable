import type { Page } from '@playwright/test';

/** Matches admin `useMobileAdmin` / WP admin mobile breakpoint. */
export const ADMIN_MOBILE_VIEWPORT = { width: 390, height: 844 };

export async function useAdminMobileViewport(page: Page): Promise<void> {
  await page.setViewportSize(ADMIN_MOBILE_VIEWPORT);
}

/** Hash navigation for the single-page admin app. */
export async function gotoAdminRoute(page: Page, adminBase: string, route: string): Promise<void> {
  const hash = route.startsWith('#') ? route : `#${route.startsWith('/') ? route : `/${route}`}`;
  const base = adminBase.replace(/#.*$/, '');
  await page.goto(`${base}${hash}`);
}

export function adminNavLink(page: Page, label: string | RegExp) {
  return page.locator('.mrt-admin-shell__menu a', { hasText: label });
}
