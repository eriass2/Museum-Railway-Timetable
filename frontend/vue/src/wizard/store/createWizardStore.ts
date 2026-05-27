import { computed } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import { wizardCfg } from '../utils/wizardLabels';
import { buildWizardStoreState } from './wizardStoreState';
import type { WizardInjection } from './wizardStoreTypes';

export type { WizardStore, WizardInjection } from './wizardStoreTypes';

export function createWizardStore(config: WizardVueConfig): WizardInjection {
  const cfg = computed(() => wizardCfg(config));
  const store = buildWizardStoreState(config, cfg);
  return { config, cfg, store };
}
