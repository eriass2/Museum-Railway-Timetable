import type { WizardVueConfig } from '../../config/types';
import { resolveMrtString } from '../../utils/mrtStrings';
import type { JourneyConnection, WizardStep } from '../types';
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
  fromId: number,
  toId: number,
): boolean {
  if (!fromId || !toId) {
    state.showError(cfgStr(cfg, 'pleaseStations', 'Välj både avrese- och ankomststation.'));
    return false;
  }
  if (fromId === toId) {
    state.showError(
      resolveMrtString(
        config,
        'errorSameStations',
        cfgStr(cfg, 'errorGeneric', 'Ogiltiga stationer.'),
      ),
    );
    return false;
  }
  return true;
}

export function resetRouteSelections(state: {
  dateYmd: string;
  outbound: JourneyConnection | null;
  inbound: JourneyConnection | null;
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
