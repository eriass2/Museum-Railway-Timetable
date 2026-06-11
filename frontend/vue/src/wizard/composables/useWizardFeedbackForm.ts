import { computed, inject, ref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import { submitWizardFeedback } from '../../api/wizardFeedback';
import { wizardKey } from '../injection';
import { cfgStr } from '../utils/wizardLabels';
import type { WizardCfgStringKey } from '../utils/wizardCfgTypes';

const MESSAGE_MIN_LENGTH = 10;

export function useWizardFeedbackForm(config: WizardVueConfig) {
  const wizard = inject(wizardKey, null);

  const open = ref(false);
  const submitting = ref(false);
  const sent = ref(false);
  const error = ref('');
  const type = ref<'bug' | 'suggestion'>('bug');
  const message = ref('');
  const email = ref('');
  const website = ref('');

  const messageLength = computed(() => message.value.trim().length);
  const canSubmit = computed(() => messageLength.value >= MESSAGE_MIN_LENGTH && !submitting.value);
  const showMessageHint = computed(
    () => messageLength.value > 0 && messageLength.value < MESSAGE_MIN_LENGTH,
  );

  function label(key: WizardCfgStringKey, fallback: string): string {
    return cfgStr(wizard?.cfg ?? {}, key, fallback);
  }

  function feedbackContext(): Record<string, string | number> {
    const store = wizard?.store;
    if (!store) {
      return {};
    }
    return {
      fromStationId: store.fromId,
      toStationId: store.toId,
      date: store.dateYmd,
      tripType: store.tripType,
    };
  }

  function resetForm(): void {
    type.value = 'bug';
    message.value = '';
    email.value = '';
    website.value = '';
    error.value = '';
  }

  function closePanel(): void {
    open.value = false;
    sent.value = false;
    resetForm();
  }

  async function submit(): Promise<void> {
    if (!canSubmit.value) {
      return;
    }
    submitting.value = true;
    error.value = '';
    const response = await submitWizardFeedback(config, {
      type: type.value,
      message: message.value,
      email: email.value,
      website: website.value,
      pageUrl: window.location.href,
      wizardStep: wizard?.store.step ?? '',
      context: feedbackContext(),
    });
    submitting.value = false;
    if (!response.success || !response.data?.saved) {
      error.value =
        response.message || label('feedbackError', 'Kunde inte skicka rapporten. Försök igen.');
      return;
    }
    sent.value = true;
    resetForm();
  }

  return {
    MESSAGE_MIN_LENGTH,
    open,
    submitting,
    sent,
    error,
    type,
    message,
    email,
    website,
    messageLength,
    canSubmit,
    showMessageHint,
    label,
    closePanel,
    submit,
  };
}
