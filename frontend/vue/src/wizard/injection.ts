import type { InjectionKey } from 'vue';
import type { WizardInjection } from './store/createWizardStore';

export const wizardKey: InjectionKey<WizardInjection> = Symbol('mrtWizard');
