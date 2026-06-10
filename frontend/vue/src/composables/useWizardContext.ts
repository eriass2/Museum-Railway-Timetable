import { inject } from 'vue';
import { wizardKey } from '../wizard/injection';
import type { WizardInjection } from '../wizard/store/createWizardStore';

export function useWizardContext(): WizardInjection {
  const ctx = inject(wizardKey);
  if (!ctx) {
    throw new Error('useWizardContext must be used inside JourneyWizardApp');
  }
  return ctx;
}
