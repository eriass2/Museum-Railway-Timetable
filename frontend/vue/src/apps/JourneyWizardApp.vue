<script setup lang="ts">
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue';
import { applyWizardDebugPreset } from '../wizard/composables/useWizardDebug';
import type { WizardVueConfig } from '../config/types';
import { useMrtAjax } from '../composables/useMrtAjax';
import { createWizardStore } from '../wizard/store/createWizardStore';
import { wizardKey } from '../wizard/injection';
import WizardStepNav from '../wizard/components/WizardStepNav.vue';
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
const embedded = Boolean(props.config.embedded);
const debug = String(props.config.debug || '');
const ticketUrl = String(props.config.ticketUrl || '');
const heroSubtitle = String(props.config.heroSubtitle || '');
const timetableId = Number(props.config.timetableId) || 0;
const timetableHtml = ref('');
const showTimetable = computed(() => timetableId > 0 && Boolean(timetableHtml.value));
const panelsRef = ref<HTMLElement | null>(null);

const { run: fetchOverview } = useMrtAjax(props.config);

watch(
  () => store.step,
  async () => {
    await nextTick();
    const panel = panelsRef.value?.querySelector('.mrt-journey-wizard__panel--active');
    const heading = panel?.querySelector('h2, h3') as HTMLElement | null;
    if (!heading) {
      return;
    }
    heading.setAttribute('tabindex', '-1');
    heading.focus();
    heading.addEventListener('blur', () => heading.removeAttribute('tabindex'), { once: true });
  },
);

onMounted(async () => {
  if (debug) {
    applyWizardDebugPreset(wizardCtx, debug);
  }
  if (timetableId <= 0) {
    return;
  }
  const res = await fetchOverview<{ html: string }>('mrt_timetable_overview_html', {
    timetable_id: timetableId,
  });
  if (res.success && res.data?.html) {
    timetableHtml.value = res.data.html;
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
    :data-ticket-url="ticketUrl"
    :data-start-of-week="String(config.startOfWeek ?? 1)"
    :data-wizard-debug="debug || undefined"
  >
    <section class="mrt-journey-wizard__hero">
      <div class="mrt-journey-wizard__hero-inner">
        <noscript>
          <p class="mrt-alert mrt-alert-info">
            {{ cfgStr(cfg, 'needsJs', 'Reseplaneraren kräver JavaScript.') }}
          </p>
        </noscript>
        <div v-if="store.error" class="mrt-journey-wizard__errors" role="alert" aria-live="assertive">
          <div class="mrt-alert mrt-alert-error">{{ store.error }}</div>
        </div>
        <WizardStepNav />
        <div ref="panelsRef" class="mrt-journey-wizard__panels">
          <WizardRouteStep
            v-if="store.step === 'route'"
            :stations="stations"
            :hero-subtitle="heroSubtitle"
            :timetable-html="timetableHtml"
            :show-timetable="showTimetable"
          />
          <WizardDateStep v-else-if="store.step === 'date'" />
          <WizardTripStep v-else-if="store.step === 'outbound'" leg-ctx="outbound" />
          <WizardTripStep v-else-if="store.step === 'return'" leg-ctx="return" />
          <WizardSummaryStep v-else-if="store.step === 'summary'" :ticket-url="ticketUrl" />
        </div>
      </div>
    </section>
  </div>
</template>
