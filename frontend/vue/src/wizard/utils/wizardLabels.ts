import type { MrtVueConfig } from '../../useMrtConfig';

export type WizardCfg = Record<string, unknown>;

export function wizardCfg(config: MrtVueConfig): WizardCfg {
  const wizard = (config.wizard || {}) as WizardCfg;
  const labels = (config.labels || {}) as WizardCfg;
  return { ...wizard, ...labels };
}

export function cfgStr(cfg: WizardCfg, key: string, fallback = ''): string {
  const v = cfg[key];
  return typeof v === 'string' ? v : fallback;
}
