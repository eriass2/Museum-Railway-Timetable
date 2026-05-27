import type { TripType, WizardStep } from '../types';
import { cfgStr, type WizardCfg } from '../utils/wizardLabels';

export function buildStepSequence(tripType: TripType): WizardStep[] {
  const seq: WizardStep[] = ['route', 'date', 'outbound'];
  if (tripType === 'return') {
    seq.push('return');
  }
  seq.push('summary');
  return seq;
}

export function buildStepLabels(cfg: WizardCfg): Record<WizardStep, string> {
  return {
    route: cfgStr(cfg, 'stepRoute', 'Sök resa'),
    date: cfgStr(cfg, 'stepDate', 'Datum'),
    outbound: cfgStr(cfg, 'stepOutbound', 'Utresa'),
    return: cfgStr(cfg, 'stepReturn', 'Återresa'),
    summary: cfgStr(cfg, 'stepSummary', 'Sammanfattning'),
  };
}
