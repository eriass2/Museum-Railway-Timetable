<script setup lang="ts">
import { computed, inject, ref } from 'vue';
import type { WizardVueConfig } from '../../config/types';
import { submitWizardFeedback } from '../../api/wizardFeedback';
import { wizardKey } from '../injection';
import { cfgStr } from '../utils/wizardLabels';
import type { WizardCfgStringKey } from '../utils/wizardCfgTypes';

const props = defineProps<{ config: WizardVueConfig }>();
const wizard = inject(wizardKey, null);

const open = ref(false);
const submitting = ref(false);
const sent = ref(false);
const error = ref('');
const type = ref<'bug' | 'suggestion'>('bug');
const message = ref('');
const email = ref('');
const website = ref('');

const canSubmit = computed(() => message.value.trim().length >= 10 && !submitting.value);

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
  const response = await submitWizardFeedback(props.config, {
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
    error.value = response.message || label('feedbackError', 'Kunde inte skicka rapporten. Försök igen.');
    return;
  }
  sent.value = true;
  resetForm();
}
</script>

<template>
  <div class="mrt-wizard-feedback">
    <button
      type="button"
      class="mrt-wizard-feedback__fab"
      :aria-expanded="open"
      @click="open = true"
    >
      {{ label('feedbackButton', 'Rapportera fel eller förslag') }}
    </button>

    <div v-if="open" class="mrt-wizard-feedback__backdrop" @click.self="closePanel">
      <section class="mrt-wizard-feedback__panel" role="dialog" aria-modal="true">
        <header class="mrt-wizard-feedback__header">
          <h2>{{ label('feedbackTitle', 'Rapportera fel eller förslag') }}</h2>
          <button type="button" class="mrt-wizard-feedback__close" @click="closePanel">×</button>
        </header>

        <p v-if="sent" class="mrt-wizard-feedback__thanks" role="status">
          {{ label('feedbackThanks', 'Tack! Vi har tagit emot din rapport.') }}
        </p>

        <form v-else class="mrt-wizard-feedback__form" @submit.prevent="submit">
          <fieldset class="mrt-wizard-feedback__types">
            <legend>Typ</legend>
            <label>
              <input v-model="type" type="radio" value="bug" />
              {{ label('feedbackTypeBug', 'Fel / bugg') }}
            </label>
            <label>
              <input v-model="type" type="radio" value="suggestion" />
              {{ label('feedbackTypeSuggestion', 'Förslag') }}
            </label>
          </fieldset>

          <label class="mrt-wizard-feedback__field">
            <span>{{ label('feedbackMessage', 'Beskrivning') }} *</span>
            <textarea v-model="message" required minlength="10" rows="5" />
          </label>

          <label class="mrt-wizard-feedback__field">
            <span>{{ label('feedbackEmail', 'E-post (valfritt)') }}</span>
            <input v-model="email" type="email" autocomplete="email" />
          </label>

          <label class="mrt-wizard-feedback__honeypot" aria-hidden="true">
            Website
            <input v-model="website" type="text" tabindex="-1" autocomplete="off" />
          </label>

          <p class="mrt-wizard-feedback__privacy">
            {{ label('feedbackPrivacy', 'Vi sparar din rapport för felsökning. E-post används bara om du fyller i den.') }}
          </p>
          <p v-if="error" class="mrt-wizard-feedback__error" role="alert">{{ error }}</p>

          <div class="mrt-wizard-feedback__actions">
            <button type="button" class="mrt-wizard-feedback__secondary" @click="closePanel">
              {{ label('feedbackCancel', 'Avbryt') }}
            </button>
            <button type="submit" class="mrt-wizard-feedback__primary" :disabled="!canSubmit">
              {{ label('feedbackSubmit', 'Skicka') }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>
