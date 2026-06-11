<script setup lang="ts">
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue';
import MrtAlert from '../components/ui/MrtAlert.vue';
import MrtStepProgress from '../components/ui/MrtStepProgress.vue';
import { applyWizardDebugPreset } from '../wizard/composables/useWizardDebug';
import '../styles/journey-wizard.css';
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
import { logWizardStepInteractive } from '../wizard/utils/wizardStepTiming';

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

const heroBackgroundUrl = computed(() => {
  if (embedded) {
    return '';
  }
  return String(props.config.heroBackgroundUrl || '').trim();
});

const heroSectionStyle = computed(() => {
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

watch(
  () => store.step,
  async (step) => {
    const started = performance.now();
    await nextTick();
    const panel = panelsRef.value?.querySelector('.mrt-step-panel--active');
    if (panel) {
      logWizardStepInteractive(props.config, step, performance.now() - started);
    }
    const focusEl = panel?.querySelector(
      '.mrt-step-progress__item.is-active, h2.mrt-heading--surface-title',
    ) as HTMLElement | null;
    if (!focusEl) {
      return;
    }
    focusEl.setAttribute('tabindex', '-1');
    focusEl.focus();
    focusEl.addEventListener('blur', () => focusEl.removeAttribute('tabindex'), { once: true });
  },
);

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
        <div v-else-if="store.error && store.step !== 'route'" class="mrt-journey-wizard__errors">
          <MrtAlert variant="error" live="assertive">{{ store.error }}</MrtAlert>
        </div>
        <template v-if="hasStations">
        <WizardBetaBanner v-if="betaBanner" v-bind="betaBanner" />
        <MrtStepProgress
          :items="progressItems"
          :nav-aria-label="cfgStr(cfg, 'stepNavAria', 'Steg i reseplaneraren')"
          :readonly="false"
          :step-go-to-aria="stepGoToAria"
          @select="onProgressSelect"
        />
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
        </template>
      </div>
    </section>
    <WizardFeedbackWidget v-if="config.feedbackEnabled" :config="config" />
  </div>
</template>
