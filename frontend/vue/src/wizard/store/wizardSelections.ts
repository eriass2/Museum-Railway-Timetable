import type { JourneyConnection, TripType, WizardStep } from '../types';
import { goToStep, type WizardStepState } from './wizardRoute';

export type WizardSelectionState = WizardStepState & {
  tripType: TripType;
  outbound: JourneyConnection | null;
  inbound: JourneyConnection | null;
};

export function applyOutboundSelection(
  state: WizardSelectionState,
  conn: JourneyConnection,
): void {
  state.outbound = conn;
  state.inbound = null;
  const next: WizardStep = state.tripType === 'return' ? 'return' : 'summary';
  goToStep(state, next);
}

export function applyInboundSelection(
  state: WizardSelectionState,
  conn: JourneyConnection,
): void {
  state.inbound = conn;
  goToStep(state, 'summary');
}
