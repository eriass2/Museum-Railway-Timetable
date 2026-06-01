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
import WizardSummaryStep from '../wizard/components/WizardSummaryStep.vue';
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
const panelsRef = ref<HTMLElement | null>(null);

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
  async () => {
    await nextTick();
    const panel = panelsRef.value?.querySelector('.mrt-step-panel--active');
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
    <section class="mrt-journey-wizard__hero">
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
        <MrtStepProgress
          :items="progressItems"
          :nav-aria-label="cfgStr(cfg, 'stepNavAria', 'Steg i reseplaneraren')"
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
  </div>
</template>
