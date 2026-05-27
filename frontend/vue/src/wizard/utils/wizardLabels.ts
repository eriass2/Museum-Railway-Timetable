import type { MaybeRef } from 'vue';
import { unref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import type { WizardCfg } from './wizardCfgTypes';

export type { WizardCfg, DebugPreset, PriceMatrix, L10nMap } from './wizardCfgTypes';

export function wizardCfg(config: WizardVueConfig): WizardCfg {
  const wizard = config.wizard ?? {};
  const labels = config.labels ?? {};
  return { ...wizard, ...labels } as WizardCfg;
}

export function cfgStr(cfg: MaybeRef<WizardCfg>, key: string, fallback = ''): string {
  const v = unref(cfg)[key as keyof WizardCfg];
  return typeof v === 'string' ? v : fallback;
}

export function cfgStringArray(cfg: WizardCfg, key: keyof WizardCfg): string[] {
  const v = cfg[key];
  if (!Array.isArray(v)) {
    return [];
  }
  return v.filter((item): item is string => typeof item === 'string');
}

export function cfgRecord(cfg: WizardCfg, key: keyof WizardCfg): Record<string, string> {
  const v = cfg[key];
  if (!v || typeof v !== 'object' || Array.isArray(v)) {
    return {};
  }
  const out: Record<string, string> = {};
  for (const [k, val] of Object.entries(v)) {
    if (typeof val === 'string') {
      out[k] = val;
    }
  }
  return out;
}
