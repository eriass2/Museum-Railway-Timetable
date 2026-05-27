import type { MaybeRef } from 'vue';
import { unref } from 'vue';
import type { WizardVueConfig } from '../../config/types';

export type WizardCfg = Record<string, unknown>;

export function wizardCfg(config: WizardVueConfig): WizardCfg {
  const wizard = (config.wizard || {}) as WizardCfg;
  const labels = (config.labels || {}) as WizardCfg;
  return { ...wizard, ...labels };
}

export function cfgStr(cfg: MaybeRef<WizardCfg>, key: string, fallback = ''): string {
  const v = unref(cfg)[key];
  return typeof v === 'string' ? v : fallback;
}
