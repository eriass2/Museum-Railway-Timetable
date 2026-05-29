/** Demo page URL for WordPress E2E (docker CI or local compose). */
export const wpDemoUrl =
  process.env.MRT_E2E_WP_DEMO_URL ||
  process.env.MRT_E2E_WP_OVERVIEW_URL ||
  process.env.MRT_E2E_WP_MONTH_URL ||
  process.env.MRT_E2E_WP_WIZARD_URL;
