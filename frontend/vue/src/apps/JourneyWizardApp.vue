<script setup lang="ts">
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue';
import { applyWizardDebugPreset } from '../wizard/composables/useWizardDebug';
import type { MrtVueConfig } from '../useMrtConfig';
import { mrtPost } from '../api/mrtApi';
import { useWizard } from '../wizard/composables/useWizard';
import { wizardKey } from '../wizard/injection';
import WizardStepNav from '../wizard/components/WizardStepNav.vue';
import WizardRouteStep from '../wizard/components/WizardRouteStep.vue';
import WizardDateStep from '../wizard/components/WizardDateStep.vue';
import WizardTripStep from '../wizard/components/WizardTripStep.vue';
import WizardSummaryStep from '../wizard/components/WizardSummaryStep.vue';
import { cfgStr } from '../wizard/utils/wizardLabels';

const props = defineProps<{ config: MrtVueConfig }>();

const wizard = useWizard(props.config);
provide(wizardKey, wizard);

const step = computed(() => wizard.step.value);
const wizardError = computed(() => wizard.error.value);

const stations = computed(() => (props.config.stations || []) as { id: number; title: string }[]);
const embedded = computed(() => Boolean(props.config.embedded));
const debug = computed(() => String(props.config.debug || ''));
const ticketUrl = computed(() => String(props.config.ticketUrl || ''));
const heroSubtitle = computed(() => String(props.config.heroSubtitle || ''));
const timetableId = computed(() => Number(props.config.timetableId) || 0);
const timetableHtml = ref('');
const showTimetable = computed(() => timetableId.value > 0 && Boolean(timetableHtml.value));
const panelsRef = ref<HTMLElement | null>(null);

watch(
  () => wizard.step.value,
  async () => {
    await nextTick();
    const panel = panelsRef.value?.querySelector(
      '.mrt-jw-panel--active, .mrt-journey-wizard__panel--active',
    );
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
  if (debug.value) {
    applyWizardDebugPreset(wizard, debug.value);
  }
  if (timetableId.value <= 0) {
    return;
  }
  const res = await mrtPost<{ html: string }>(props.config, 'mrt_timetable_overview_html', {
    timetable_id: timetableId.value,
  });
  if (res.success && res.data?.html) {
    timetableHtml.value = res.data.html;
  }
});
</script>

<template>
  <div
    class="mrt-journey-wizard mrt-my-lg"
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
            {{ cfgStr(wizard.cfg, 'needsJs', 'This planner needs JavaScript.') }}
          </p>
        </noscript>
        <div v-if="wizardError" class="mrt-journey-wizard__errors" role="alert" aria-live="assertive">
          <div class="mrt-alert mrt-alert-error">{{ wizardError }}</div>
        </div>
        <WizardStepNav />
        <div ref="panelsRef" class="mrt-journey-wizard__panels">
          <WizardRouteStep
            v-if="step === 'route'"
            :stations="stations"
            :hero-subtitle="heroSubtitle"
            :timetable-html="timetableHtml"
            :show-timetable="showTimetable"
          />
          <WizardDateStep v-else-if="step === 'date'" />
          <WizardTripStep v-else-if="step === 'outbound'" leg-ctx="outbound" />
          <WizardTripStep v-else-if="step === 'return'" leg-ctx="return" />
          <WizardSummaryStep v-else-if="step === 'summary'" :ticket-url="ticketUrl" />
        </div>
      </div>
    </section>
  </div>
</template>
