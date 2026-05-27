import type { WizardStep, TripType } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import { cfgStr, cfgStringArray } from '../utils/wizardLabels';
import { formatYmdForDisplay } from '../utils/wizardDate';
import { buildStepLabels, buildStepSequence } from './wizardSteps';

export type WizardContextState = {
  tripType: TripType;
  fromTitle: string;
  toTitle: string;
  dateYmd: string;
};

export function wizardStepSequence(tripType: TripType): WizardStep[] {
  return buildStepSequence(tripType);
}

export function wizardStepLabels(cfg: WizardCfg): Record<WizardStep, string> {
  return buildStepLabels(cfg);
}

export function wizardContextLine(state: WizardContextState, cfg: WizardCfg): string {
  const trip =
    state.tripType === 'return'
      ? cfgStr(cfg, 'tripTypeReturn', 'Tur- och retur')
      : cfgStr(cfg, 'tripTypeSingle', 'Enkel');
  const route = `${state.fromTitle} → ${state.toTitle} | ${trip}`;
  if (!state.dateYmd) {
    return route;
  }
  const human = formatYmdForDisplay(state.dateYmd, cfgStringArray(cfg, 'monthNames'));
  return `${route}\n${human}`;
}
