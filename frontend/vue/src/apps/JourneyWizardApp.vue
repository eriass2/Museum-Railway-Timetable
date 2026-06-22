<script setup lang="ts">
import { computed, onMounted, provide, ref } from 'vue';
import MrtStack from '../components/ui/MrtStack.vue';
import MrtWizardMainCard from '../components/layout/MrtWizardMainCard.vue';
import MrtWizardShell from '../components/layout/MrtWizardShell.vue';
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
  <MrtStack
    margin-top="lg"
    margin-bottom="lg"
    :data-start-of-week="String(config.startOfWeek ?? 1)"
    :data-wizard-debug="debug || undefined"
  >
    <MrtWizardShell
      :embedded="embedded"
      :step="store.step"
      :debug="debug !== ''"
      :hero-background-url="heroBackgroundUrl"
      :bleed-background="bleedBackground"
    >
      <noscript>
        <MrtAlert variant="info">
          {{ cfgStr(cfg, 'needsJs', 'Reseplaneraren kräver JavaScript.') }}
        </MrtAlert>
      </noscript>
      <MrtAlert v-if="!hasStations" variant="info">
        {{ cfgStr(cfg, 'noStations', 'Inga stationer är tillgängliga.') }}
      </MrtAlert>
      <MrtWizardMainCard v-if="hasStations" :embedded="embedded">
        <WizardBetaBanner v-if="betaBanner" v-bind="betaBanner" />
        <MrtStepProgress
          :items="progressItems"
          :nav-aria-label="cfgStr(cfg, 'stepNavAria', 'Steg i reseplaneraren')"
          :readonly="false"
          :step-go-to-aria="stepGoToAria"
          @select="onProgressSelect"
        />
        <div v-if="store.error && store.step !== 'route'" class="mrt-wizard-main-card__errors">
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
        <WizardFeedbackWidget v-if="config.feedbackEnabled" :config="config" />
      </MrtWizardMainCard>
    </MrtWizardShell>
  </MrtStack>
</template>

<style scoped>
.mrt-wizard-main-card__errors {
  max-width: var(--mrt-wizard-errors-max-width, var(--mrt-max-step));
  margin: 0 auto 0.75rem;
}

.mrt-journey-wizard__panels {
  min-width: 0;
  max-width: 100%;
}
</style>

<style>
@import '../wizard/styles/wizardStepSurfaces.css';
</style>
