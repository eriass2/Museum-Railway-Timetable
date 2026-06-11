import { computed, ref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import { createWizardResourceCache } from '../cache/resourceCache';
import { wizardCfg } from '../utils/wizardLabels';
import { buildWizardStoreState } from './wizardStoreState';
import type { WizardInjection } from './wizardStoreTypes';

export type { WizardStore, WizardInjection } from './wizardStoreTypes';

export function createWizardStore(config: WizardVueConfig): WizardInjection {
  const cfg = computed(() => wizardCfg(config));
  const store = buildWizardStoreState(config, cfg);
  const cacheGeneration = ref(config.cacheGeneration ?? 1);
  const resourceCache = createWizardResourceCache(cacheGeneration);
  return { config, cfg, store, resourceCache };
}
