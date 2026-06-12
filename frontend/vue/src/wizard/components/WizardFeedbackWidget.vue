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

<style scoped>
.mrt-wizard-feedback__fab {
  position: fixed;
  right: 1rem;
  bottom: 1rem;
  z-index: 40;
  min-height: 2.75rem;
  padding: 0.65rem 0.95rem;
  border: 2px solid var(--mrt-color-accent-700, #b89222);
  background: var(--mrt-wizard-yellow, #c9a227);
  color: var(--mrt-color-on-accent, #111);
  font-weight: 700;
  cursor: pointer;
}

.mrt-wizard-feedback__backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: grid;
  align-items: end;
  background: rgba(0, 0, 0, 0.45);
}

.mrt-wizard-feedback__panel {
  width: min(100%, 30rem);
  margin-inline: auto;
  padding: 1rem;
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #141414);
  box-sizing: border-box;
}

.mrt-wizard-feedback__header {
  display: flex;
  align-items: start;
  justify-content: space-between;
  gap: 1rem;
}

.mrt-wizard-feedback__header h2 {
  margin: 0 0 0.75rem;
  font-size: 1.25rem;
}

.mrt-wizard-feedback__close {
  border: 0;
  background: transparent;
  color: inherit;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
}

.mrt-wizard-feedback__form,
.mrt-wizard-feedback__field {
  display: grid;
  gap: 0.55rem;
}

.mrt-wizard-feedback__types {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1rem;
  margin: 0;
  padding: 0;
  border: 0;
}

.mrt-wizard-feedback__types legend,
.mrt-wizard-feedback__field span {
  font-weight: 700;
}

.mrt-wizard-feedback__field textarea,
.mrt-wizard-feedback__field input {
  width: 100%;
  padding: 0.55rem;
  border: 2px solid var(--mrt-color-border-on-surface, #767676);
  box-sizing: border-box;
}

.mrt-wizard-feedback__honeypot {
  position: absolute;
  left: -9999px;
}

.mrt-wizard-feedback__hint {
  margin: 0;
  font-size: 0.875rem;
  color: var(--mrt-color-muted-text, #5a5a5a);
}

.mrt-wizard-feedback__hint--warn {
  color: var(--mrt-color-error-text, #7a1212);
  font-weight: 700;
}

.mrt-wizard-feedback__privacy,
.mrt-wizard-feedback__thanks {
  margin: 0;
}

.mrt-wizard-feedback__error {
  margin: 0;
  color: var(--mrt-color-error-text, #7a1212);
  font-weight: 700;
}

.mrt-wizard-feedback__actions {
  display: flex;
  justify-content: end;
  gap: 0.75rem;
}

.mrt-wizard-feedback__primary,
.mrt-wizard-feedback__secondary {
  min-height: 2.5rem;
  padding: 0.5rem 0.85rem;
  border: 2px solid var(--mrt-color-accent-700, #b89222);
  font-weight: 700;
  cursor: pointer;
}

.mrt-wizard-feedback__primary {
  background: var(--mrt-wizard-yellow, #c9a227);
  color: var(--mrt-color-on-accent, #111);
}

.mrt-wizard-feedback__primary:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.mrt-wizard-feedback__secondary {
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #141414);
}

@media (min-width: 40rem) {
  .mrt-wizard-feedback__backdrop {
    align-items: center;
  }
}
</style>
