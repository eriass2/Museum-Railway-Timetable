import type { InjectionKey } from 'vue';
import type { WizardContext } from './composables/useWizard';

export const wizardKey: InjectionKey<WizardContext> = Symbol('mrtWizard');
