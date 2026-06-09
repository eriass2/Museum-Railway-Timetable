import type { MrtRestConfig } from '../../config/types';
import { configureMrtLog, mrtLog } from '../../utils/mrtLog';
import type { WizardStep } from '../types';

/** Log time from step change until active panel is in DOM (dev only). */
export function logWizardStepInteractive(
  config: MrtRestConfig,
  step: WizardStep,
  durationMs: number,
): void {
  if (!config.isDevMode) {
    return;
  }
  configureMrtLog({ isDevMode: true, defaultSource: 'wizard' });
  mrtLog({
    level: 'info',
    source: 'wizard',
    message: `wizard step interactive: ${step} ${Math.round(durationMs)}ms`,
    context: { step, durationMs: Math.round(durationMs) },
  });
}
