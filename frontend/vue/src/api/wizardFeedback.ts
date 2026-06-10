import type { MrtRestConfig } from '../config/types';
import { mrtRestRequest } from './mrtRest';

export type WizardFeedbackPayload = {
  type: 'bug' | 'suggestion';
  message: string;
  email?: string;
  pageUrl: string;
  wizardStep: string;
  context: Record<string, string | number>;
  website?: string;
};

export type WizardFeedbackResponse = {
  saved: boolean;
  id?: number;
};

export function submitWizardFeedback(config: MrtRestConfig, body: WizardFeedbackPayload) {
  return mrtRestRequest<WizardFeedbackResponse>(config, {
    method: 'POST',
    path: 'wizard/feedback',
    body,
  });
}
