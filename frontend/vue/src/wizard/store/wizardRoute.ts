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

export type WizardProgressNavState = WizardStepState & {
  stepSequence: WizardStep[];
  dateYmd: string;
  outbound: JourneyConnection | null;
  inbound: JourneyConnection | null;
};

function wizardStepIndex(sequence: WizardStep[], step: WizardStep): number {
  return sequence.indexOf(step);
}

/** Jump back to a completed step; clears downstream selections like the step back buttons. */
export function navigateToCompletedWizardStep(
  state: WizardProgressNavState,
  target: WizardStep,
): boolean {
  const sequence = state.stepSequence;
  const currentIndex = wizardStepIndex(sequence, state.step);
  const targetIndex = wizardStepIndex(sequence, target);
  if (targetIndex < 0 || targetIndex >= currentIndex) {
    return false;
  }

  state.clearError();

  const routeIndex = wizardStepIndex(sequence, 'route');
  const dateIndex = wizardStepIndex(sequence, 'date');
  const outboundIndex = wizardStepIndex(sequence, 'outbound');
  const returnIndex = wizardStepIndex(sequence, 'return');

  if (targetIndex <= routeIndex) {
    state.dateYmd = '';
    state.outbound = null;
    state.inbound = null;
  } else if (targetIndex <= dateIndex) {
    state.outbound = null;
    state.inbound = null;
  } else if (targetIndex <= outboundIndex) {
    state.outbound = null;
    state.inbound = null;
  } else if (returnIndex >= 0 && targetIndex <= returnIndex) {
    state.inbound = null;
  }

  state.step = target;
  return true;
}
