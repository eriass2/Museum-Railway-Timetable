<script setup lang="ts">
import { computed, onMounted, provide, ref } from 'vue';
import MrtPublicAppShell from '../components/layout/MrtPublicAppShell.vue';
import MrtAlert from '../components/ui/MrtAlert.vue';
import MrtStepProgress from '../components/ui/MrtStepProgress.vue';
import { applyWizardDebugPreset } from '../wizard/composables/useWizardDebug';
import { useWizardStepFocus } from '../wizard/composables/useWizardStepFocus';
import type { WizardVueConfig } from '../config/types';
import { createWizardStore } from '../wizard/store/createWizardStore';
import { wizardKey } from '../wizard/injection';
import WizardRouteStep from '../wizard/components/WizardRouteStep.vue';
import WizardDateStep from '../wizard/components/WizardDateStep.vue';
import WizardTripStep from '../wizard/components/WizardTripStep.vue';
import WizardBetaBanner from '../wizard/components/WizardBetaBanner.vue';
import WizardFeedbackWidget from '../wizard/components/WizardFeedbackWidget.vue';
import WizardSummaryStep from '../wizard/components/WizardSummaryStep.vue';
import type { WizardStep } from '../wizard/types';
import { cfgStr } from '../wizard/utils/wizardLabels';

const props = defineProps<{ config: WizardVueConfig }>();

const wizardCtx = createWizardStore(props.config);
provide(wizardKey, wizardCtx);

const { store, cfg } = wizardCtx;

const stations = props.config.stations || [];
const hasStations = stations.length > 0;
const embedded = Boolean(props.config.embedded);
const debug = String(props.config.debug || '');
const timetablePageUrl = String(props.config.timetablePageUrl || '');
const betaBanner = props.config.betaBanner ?? null;
const panelsRef = ref<HTMLElement | null>(null);

const heroBackgroundUrl = computed(() => String(props.config.heroBackgroundUrl || '').trim());

const bleedBackground = computed(() => !embedded && heroBackgroundUrl.value !== '');

const heroSectionStyle = computed(() => {
  if (bleedBackground.value) {
    return undefined;
  }
  const url = heroBackgroundUrl.value;
  if (!url) {
    return undefined;
  }
  return { '--mrt-wizard-hero-bg-image': `url(${JSON.stringify(url)})` };
});

const progressItems = computed(() => {
  const currentIndex = store.stepSequence.indexOf(store.step);
  return store.stepSequence.map((key, i) => ({
    key,
    label: `${i + 1}. ${store.stepLabels[key]}`,
    active: store.step === key,
    done: currentIndex > i,
  }));
});

useWizardStepFocus(props.config, () => store.step, panelsRef);

function stepGoToAria(label: string): string {
  const template = cfgStr(cfg.value, 'stepGoTo', 'Gå till steg: %s');
  return template.includes('%s') ? template.replace('%s', label) : `${template} ${label}`;
}

function onProgressSelect(key: string): void {
  store.navigateToStep(key as WizardStep);
}

onMounted(() => {
  if (debug) {
    applyWizardDebugPreset(wizardCtx, debug);
  }
});
</script>

<template>
  <div
    class="mrt-journey-wizard mrt-my-lg"
    :data-step="store.step"
    :class="{
      'mrt-journey-wizard--embedded': embedded,
      'mrt-journey-wizard--debug': debug !== '',
    }"
    :data-start-of-week="String(config.startOfWeek ?? 1)"
    :data-wizard-debug="debug || undefined"
  >
    <MrtPublicAppShell
      :bleed-background="bleedBackground"
      :background-image="heroBackgroundUrl"
    >
      <section
        class="mrt-journey-wizard__hero"
        :class="{ 'mrt-journey-wizard__hero--has-bg': heroBackgroundUrl !== '' }"
        :style="heroSectionStyle"
      >
        <div class="mrt-journey-wizard__hero-inner">
          <noscript>
            <MrtAlert variant="info">
              {{ cfgStr(cfg, 'needsJs', 'Reseplaneraren kräver JavaScript.') }}
            </MrtAlert>
          </noscript>
          <MrtAlert v-if="!hasStations" variant="info">
            {{ cfgStr(cfg, 'noStations', 'Inga stationer är tillgängliga.') }}
          </MrtAlert>
          <template v-if="hasStations">
            <div class="mrt-journey-wizard__main-card">
              <WizardBetaBanner v-if="betaBanner" v-bind="betaBanner" />
              <MrtStepProgress
                :items="progressItems"
                :nav-aria-label="cfgStr(cfg, 'stepNavAria', 'Steg i reseplaneraren')"
                :readonly="false"
                :step-go-to-aria="stepGoToAria"
                @select="onProgressSelect"
              />
              <div v-if="store.error && store.step !== 'route'" class="mrt-journey-wizard__errors">
                <MrtAlert variant="error" live="assertive">{{ store.error }}</MrtAlert>
              </div>
              <div ref="panelsRef" class="mrt-journey-wizard__panels">
                <WizardRouteStep
                  v-if="store.step === 'route'"
                  :stations="stations"
                  :timetable-page-url="timetablePageUrl"
                />
                <WizardDateStep v-else-if="store.step === 'date'" />
                <WizardTripStep v-else-if="store.step === 'outbound'" leg-ctx="outbound" />
                <WizardTripStep v-else-if="store.step === 'return'" leg-ctx="return" />
                <WizardSummaryStep v-else-if="store.step === 'summary'" />
              </div>
            </div>
          </template>
        </div>
      </section>
    </MrtPublicAppShell>
    <WizardFeedbackWidget v-if="config.feedbackEnabled" :config="config" />
  </div>
</template>

<style scoped>
.mrt-journey-wizard {
  color: var(--mrt-wizard-text);
  max-width: 100%;
  min-width: 0;
}

.mrt-journey-wizard :deep(.mrt-sr-only) {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (prefers-reduced-motion: reduce) {
  .mrt-journey-wizard :deep(*),
  .mrt-journey-wizard :deep(*::before),
  .mrt-journey-wizard :deep(*::after) {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

.mrt-journey-wizard__hero {
  box-sizing: border-box;
  position: relative;
  width: 100%;
  min-height: 0;
  padding: clamp(3rem, 8vw, 7rem) 1rem clamp(2rem, 5vw, 3rem);
  background: var(--mrt-wizard-green-dark);
}

.mrt-journey-wizard:not(.mrt-journey-wizard--embedded) .mrt-journey-wizard__hero {
  padding: clamp(2rem, 5vw, 4rem) 1rem clamp(1.5rem, 4vw, 2.5rem);
}

.mrt-journey-wizard[data-step="route"] .mrt-journey-wizard__hero {
  min-height: 0;
}

.mrt-journey-wizard__hero-inner {
  width: min(100%, 58rem);
  margin-inline: auto;
  min-width: 0;
  max-width: 100%;
}

.mrt-journey-wizard__panels {
  min-width: 0;
  max-width: 100%;
}

.mrt-journey-wizard__errors {
  max-width: 46rem;
  margin: 0 auto 0.75rem;
}

.mrt-journey-wizard :deep(.mrt-step-panel) {
  width: min(100%, 46rem);
  margin-inline: auto;
  min-width: 0;
  padding: clamp(1.5rem, 4vw, 2.75rem);
  background: var(--mrt-wizard-green-dark);
  color: #ffffff;
  box-sizing: border-box;
}

.mrt-journey-wizard :deep(.mrt-step-panel--search) {
  width: min(100%, 54rem);
  margin-top: 1.5rem;
  padding-block: clamp(1.75rem, 4vw, 3rem);
}

.mrt-journey-wizard :deep(.mrt-step-panel--wide) {
  width: min(100%, 54rem);
}

.mrt-journey-wizard :deep(.mrt-step-panel[data-wizard-step="date"]) {
  padding-bottom: clamp(1rem, 3vw, 1.5rem);
}

.mrt-journey-wizard :deep(.mrt-step-panel--search .mrt-accent-btn--primary) {
  min-width: 12rem;
  padding: 0.85rem 2rem;
  font-size: 1.05rem;
  letter-spacing: 0.04em;
}

.mrt-journey-wizard :deep(.mrt-heading--surface-title:focus) {
  outline: none;
}

.mrt-journey-wizard :deep(.mrt-heading--surface-title:focus-visible) {
  outline: 3px solid var(--mrt-wizard-focus);
  outline-offset: 4px;
}

.mrt-journey-wizard :deep(.mrt-combobox__input:focus-visible),
.mrt-journey-wizard :deep(.mrt-segmented__option:focus-visible),
.mrt-journey-wizard :deep(button:focus-visible),
.mrt-journey-wizard :deep(a:focus-visible),
.mrt-journey-wizard :deep(select:focus-visible) {
  outline: 3px solid var(--mrt-wizard-focus);
  outline-offset: 3px;
}

.mrt-journey-wizard :deep(.mrt-step-panel > .mrt-text-secondary) {
  color: var(--mrt-color-on-dark-muted);
}

.mrt-journey-wizard :deep(.mrt-mb-sm) {
  margin-bottom: 0.5rem;
}

.mrt-journey-wizard :deep(.mrt-mt-lg) {
  margin-top: 1.5rem;
}

.mrt-journey-wizard__main-card {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding: clamp(1.25rem, 3vw, 2rem);
  background: var(--mrt-wizard-green-dark, #1e4d6b);
  color: #fff;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
}

.mrt-journey-wizard__main-card > :deep(.mrt-journey-wizard__beta) {
  max-width: none;
  margin-bottom: 1rem;
}

.mrt-journey-wizard__main-card > :deep(.mrt-step-nav) {
  margin-bottom: 1.25rem;
}

.mrt-journey-wizard__main-card > .mrt-journey-wizard__errors {
  max-width: none;
}

.mrt-journey-wizard__main-card :deep(.mrt-heading--surface-title) {
  color: #fff;
}

.mrt-journey-wizard__main-card :deep(.mrt-step-panel),
.mrt-journey-wizard__main-card :deep(.mrt-step-panel--search),
.mrt-journey-wizard__main-card :deep(.mrt-step-panel--wide) {
  width: 100%;
  max-width: none;
  margin-inline: 0;
  margin-top: 0;
  padding: 0;
  background: transparent;
  color: inherit;
}

.mrt-journey-wizard__main-card :deep(.mrt-journey-wizard__route-form) {
  margin-top: 0.25rem;
}

.mrt-journey-wizard__main-card :deep(.mrt-journey-wizard__step-section) {
  margin-top: 0.5rem;
}

.mrt-journey-wizard--embedded {
  margin-top: clamp(2rem, 4vw, 3rem);
  margin-bottom: clamp(2.5rem, 5vw, 4rem);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero:not(.mrt-journey-wizard__hero--has-bg) {
  margin-left: 0;
  margin-right: 0;
  width: 100%;
  max-width: 100%;
  padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.75rem) clamp(2rem, 5vw, 3rem);
  background: var(--mrt-wizard-surface);
  color: var(--mrt-wizard-text);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero--has-bg {
  margin-left: 0;
  margin-right: 0;
  width: 100%;
  max-width: 100%;
  padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.75rem) clamp(2rem, 5vw, 3rem);
  color: #ffffff;
  position: relative;
  background-color: var(--mrt-wizard-green-dark);
  background-image: var(--mrt-wizard-hero-bg-image);
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero--has-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background: color-mix(in srgb, var(--mrt-wizard-green-dark) 30%, transparent);
  pointer-events: none;
}

.mrt-journey-wizard:not(.mrt-journey-wizard--embedded) .mrt-journey-wizard__hero--has-bg {
  background: transparent;
}

.mrt-journey-wizard__hero--has-bg > .mrt-journey-wizard__hero-inner {
  position: relative;
  z-index: 1;
}

.mrt-journey-wizard--embedded[data-step="route"] .mrt-journey-wizard__hero {
  min-height: auto;
}

.mrt-journey-wizard--embedded :deep(.mrt-step-panel--search) {
  margin-top: clamp(1rem, 2.5vw, 1.75rem);
  padding-block: clamp(2rem, 4vw, 3.25rem);
  padding-inline: clamp(1.75rem, 4vw, 3rem);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__main-card {
  box-shadow: none;
  padding: 0;
}

.mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-app-shell__content) {
  max-width: var(--mrt-wizard-content-max);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero--has-bg :deep(.mrt-step-panel--search) {
  background: var(--mrt-wizard-surface);
  color: var(--mrt-wizard-text);
  padding-inline: clamp(1.75rem, 4vw, 3rem);
}

.mrt-journey-wizard--embedded .mrt-journey-wizard__hero:not(.mrt-journey-wizard__hero--has-bg) :deep(.mrt-step-panel--search) {
  background: transparent;
  color: var(--mrt-wizard-text);
  padding-inline: 0;
}

.mrt-journey-wizard :deep(.mrt-ui-alert) {
  border-radius: 0;
}

.mrt-journey-wizard :deep(.mrt-surface--box) {
  border-radius: 0;
}

@media (min-width: 48.0625rem) {
  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) .mrt-journey-wizard__hero-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: none;
  }

  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) .mrt-journey-wizard__errors {
    max-width: none;
  }

  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-step-panel),
  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-step-panel--search),
  .mrt-journey-wizard:not(.mrt-journey-wizard--embedded) :deep(.mrt-step-panel--wide) {
    width: 100%;
    max-width: none;
    background: transparent;
  }
}

@media (max-width: 48rem) {
  .mrt-journey-wizard__hero {
    padding: 1rem;
  }

  .mrt-journey-wizard :deep(.mrt-step-panel),
  .mrt-journey-wizard :deep(.mrt-step-panel--search),
  .mrt-journey-wizard :deep(.mrt-step-panel--wide) {
    width: 100%;
    min-width: 0;
    padding: 0.75rem 0.85rem 1.1rem;
    box-sizing: border-box;
  }

  .mrt-journey-wizard :deep(.mrt-trip-summary),
  .mrt-journey-wizard :deep(.mrt-trip-summary__route) {
    min-width: 0;
    max-width: 100%;
  }

  .mrt-journey-wizard :deep(.mrt-trip-summary__route) {
    overflow-wrap: anywhere;
  }

  .mrt-journey-wizard :deep(.mrt-heading--surface-title) {
    font-size: 1.8rem;
  }

  .mrt-journey-wizard :deep(.mrt-surface:not(.mrt-surface--flush)) {
    padding: 0.85rem 0.75rem;
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
  }

  .mrt-journey-wizard :deep(.mrt-trip-card__head),
  .mrt-journey-wizard :deep(.mrt-selected-trip__card) {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.85rem 0.75rem;
  }

  .mrt-journey-wizard :deep(.mrt-trip-card__copy),
  .mrt-journey-wizard :deep(.mrt-trip-card__side) {
    width: 100%;
    min-width: 0;
  }

  .mrt-journey-wizard :deep(.mrt-trip-card__side) {
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-areas:
      "vehicles vehicles"
      "duration button";
    align-items: center;
    gap: 0.55rem 0.75rem;
  }

  .mrt-journey-wizard :deep(.mrt-vehicle-row) {
    grid-area: vehicles;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;
    gap: 0.35rem;
    max-width: 100%;
  }

  .mrt-journey-wizard :deep(.mrt-vehicle-row--compact) {
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
  }

  .mrt-journey-wizard :deep(.mrt-vehicle-row__item) {
    min-width: 0;
    max-width: 100%;
  }

  .mrt-journey-wizard :deep(.mrt-vehicle-row__icon) {
    flex-shrink: 0;
    width: 36px;
    height: 18px;
  }

  .mrt-journey-wizard :deep(.mrt-vehicle-row__item > .mrt-vehicle-row__label) {
    min-width: 0;
    overflow-wrap: anywhere;
  }

  .mrt-journey-wizard :deep(.mrt-trip-card__duration) {
    grid-area: duration;
    margin: 0;
    font-size: 1.15rem;
  }

  .mrt-journey-wizard :deep(.mrt-trip-card__side > .mrt-accent-btn) {
    grid-area: button;
    justify-self: end;
    min-width: min(100%, 8.5rem);
  }

  .mrt-journey-wizard :deep(.mrt-detail-panel) {
    padding: 0.85rem 0.5rem 0.75rem;
    overflow-x: hidden;
  }

  .mrt-journey-wizard :deep(.mrt-detail-segment__meta .mrt-vehicle-row) {
    justify-content: flex-start;
    width: 100%;
  }

  .mrt-journey-wizard :deep(.mrt-summary-list--round-trip) {
    grid-template-columns: 1fr;
  }

  .mrt-journey-wizard :deep(.mrt-price-columns--split) {
    grid-template-columns: 1fr;
  }

  @media (max-width: 22.5rem) {
    .mrt-journey-wizard :deep(.mrt-trip-card__side) {
      grid-template-columns: minmax(0, 1fr);
      grid-template-areas:
        "vehicles"
        "duration"
        "button";
    }

    .mrt-journey-wizard :deep(.mrt-trip-card__side > .mrt-accent-btn) {
      grid-area: button;
      justify-self: stretch;
      width: 100%;
    }
  }
}
</style>
