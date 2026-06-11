import type { AdminClientConfig, AdminHelpContent } from '../types';

/** Resolve help copy from admin config or fail fast in dev. */
export function requireAdminHelp(cfg: AdminClientConfig): AdminHelpContent {
  if (!cfg.help) {
    throw new Error('mrtAdminVue.help config missing');
  }
  return cfg.help;
}
