import { msg } from '../../api/mrtApi';
import type { WizardVueConfig } from '../../config/types';
import type { WizardStep } from '../types';
import { cfgStr, type WizardCfg } from '../utils/wizardLabels';

export type WizardRouteState = {
  fromId: number;
  toId: number;
  error: string;
  showError: (message: string) => void;
};

export function validateWizardRoute(
  state: WizardRouteState,
  config: WizardVueConfig,
  cfg: WizardCfg,
): boolean {
  if (!state.fromId || !state.toId) {
    state.showError(cfgStr(cfg, 'pleaseStations', 'Please select both stations.'));
    return false;
  }
  if (state.fromId === state.toId) {
    state.showError(
      msg(config, 'errorSameStations', cfgStr(cfg, 'errorGeneric', 'Invalid stations.')),
    );
    return false;
  }
  return true;
}

export function resetRouteSelections(state: {
  dateYmd: string;
  outbound: unknown;
  inbound: unknown;
}): void {
  state.dateYmd = '';
  state.outbound = null;
  state.inbound = null;
}

export type WizardStepState = {
  step: WizardStep;
  clearError: () => void;
};

export function goToStep(state: WizardStepState, next: WizardStep): void {
  state.clearError();
  state.step = next;
}
