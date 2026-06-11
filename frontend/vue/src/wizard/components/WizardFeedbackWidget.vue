<script setup lang="ts">
import type { WizardVueConfig } from '../../config/types';
import { useWizardFeedbackForm } from '../composables/useWizardFeedbackForm';

const props = defineProps<{ config: WizardVueConfig }>();

const {
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
} = useWizardFeedbackForm(props.config);
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
            <textarea
              v-model="message"
              required
              :minlength="MESSAGE_MIN_LENGTH"
              rows="5"
              :aria-describedby="showMessageHint ? 'mrt-wizard-feedback-message-hint' : undefined"
            />
            <span
              v-if="showMessageHint"
              id="mrt-wizard-feedback-message-hint"
              class="mrt-wizard-feedback__hint mrt-wizard-feedback__hint--warn"
              role="status"
            >
              {{ label('feedbackMessageTooShort', 'Minst 10 tecken krävs.') }}
              ({{ messageLength }}/{{ MESSAGE_MIN_LENGTH }})
            </span>
            <span v-else class="mrt-wizard-feedback__hint">
              {{ label('feedbackMessageHint', 'Minst 10 tecken.') }}
            </span>
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
