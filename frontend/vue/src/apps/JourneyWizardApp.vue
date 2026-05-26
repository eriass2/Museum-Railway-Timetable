<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import type { MrtVueConfig } from '../useMrtConfig';
import { mountJourneyWizard } from '../wizard/initLegacyWizard';
import { mrtPost } from '../api/mrtApi';

type Station = { id: number; title: string };

const props = defineProps<{ config: MrtVueConfig }>();

const rootRef = ref<HTMLElement | null>(null);
const timetableHtml = ref('');
const labels = computed(() => (props.config.labels || {}) as Record<string, string>);
const stations = computed(() => (props.config.stations || []) as Station[]);
const embedded = computed(() => Boolean(props.config.embedded));
const debug = computed(() => String(props.config.debug || ''));
const ticketUrl = computed(() => String(props.config.ticketUrl || ''));
const heroSubtitle = computed(() => String(props.config.heroSubtitle || ''));
const timetableId = computed(() => Number(props.config.timetableId) || 0);

onMounted(async () => {
  if (timetableId.value > 0) {
    const res = await mrtPost<{ html: string }>(props.config, 'mrt_timetable_overview_html', {
      timetable_id: timetableId.value,
    });
    if (res.success && res.data?.html) {
      timetableHtml.value = res.data.html;
    }
  }

  if (rootRef.value) {
    mountJourneyWizard(rootRef.value, props.config as Record<string, unknown>);
  }
});
</script>

<template>
  <div
    ref="rootRef"
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
          <p class="mrt-alert mrt-alert-info">{{ labels.needsJs || 'This planner needs JavaScript.' }}</p>
        </noscript>
        <div class="mrt-journey-wizard__errors" role="alert" aria-live="assertive" />
        <nav class="mrt-journey-wizard__nav" :aria-label="labels.stepNavAria || 'Trip planner steps'">
          <ol class="mrt-journey-wizard__steps" data-wizard-steps />
        </nav>
        <div class="mrt-journey-wizard__panels">
          <div
            class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active mrt-journey-wizard__search-panel"
            :class="{ 'mrt-journey-wizard__search-panel--with-timetable': timetableId > 0 }"
            data-wizard-step="route"
            role="region"
          >
            <header class="mrt-journey-wizard__hero-head">
              <h2 class="mrt-journey-wizard__hero-title">
                {{ labels.routeTitle || 'Sök din resa med Lennakatten' }}
              </h2>
              <p v-if="heroSubtitle" class="mrt-journey-wizard__hero-lede">{{ heroSubtitle }}</p>
            </header>
            <div class="mrt-form-fields mrt-journey-wizard__route">
              <div class="mrt-form-field">
                <label for="mrt_wizard_from">{{ labels.from || 'Från' }}</label>
                <select id="mrt_wizard_from" name="mrt_wizard_from" required>
                  <option value="">{{ labels.fromPlaceholder || '' }}</option>
                  <option v-for="s in stations" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
              </div>
              <div class="mrt-form-field">
                <label for="mrt_wizard_to">{{ labels.to || 'Till' }}</label>
                <select id="mrt_wizard_to" name="mrt_wizard_to" required>
                  <option value="">{{ labels.toPlaceholder || '' }}</option>
                  <option v-for="s in stations" :key="'t-' + s.id" :value="s.id">{{ s.title }}</option>
                </select>
              </div>
              <fieldset class="mrt-form-field mrt-journey-wizard__trip-type">
                <legend class="mrt-sr-only">{{ labels.tripTypeLegend || 'Restyp' }}</legend>
                <div class="mrt-journey-wizard__trip-type-toggle">
                  <label class="mrt-journey-wizard__radio-label">
                    <input type="radio" name="mrt_wizard_trip_type" value="single" checked>
                    <span class="mrt-journey-wizard__radio-text" aria-hidden="true">→</span>
                    <span class="mrt-journey-wizard__radio-text">{{ labels.tripSingle || 'Enkel' }}</span>
                  </label>
                  <label class="mrt-journey-wizard__radio-label">
                    <input type="radio" name="mrt_wizard_trip_type" value="return">
                    <span class="mrt-journey-wizard__radio-text" aria-hidden="true">↔</span>
                    <span class="mrt-journey-wizard__radio-text">{{ labels.tripReturn || 'Tur och retur' }}</span>
                  </label>
                </div>
              </fieldset>
              <div class="mrt-form-field mrt-journey-wizard__actions">
                <button type="button" class="mrt-btn mrt-btn--primary mrt-journey-wizard__cta" data-wizard-next="route">
                  {{ labels.searchTrip || 'Sök resa' }}
                </button>
              </div>
            </div>
            <details v-if="timetableId > 0 && timetableHtml" class="mrt-journey-wizard__timetable">
              <summary class="mrt-journey-wizard__timetable-summary">
                {{ labels.showTimetable || 'Visa tidtabell' }}
              </summary>
              <div class="mrt-journey-wizard__timetable-body" v-html="timetableHtml" />
            </details>
          </div>

          <div class="mrt-jw-panel mrt-journey-wizard__panel" data-wizard-step="date" role="region" hidden>
            <header class="mrt-jw-step-head mrt-journey-wizard__step-head">
              <button type="button" class="mrt-jw-btn mrt-jw-btn--back mrt-journey-wizard__back" data-wizard-back="date">
                {{ labels.back || '← Tillbaka' }}
              </button>
              <p class="mrt-jw-step-head__context mrt-journey-wizard__context" data-wizard-context />
            </header>
            <h3 class="mrt-jw-typo mrt-jw-typo--step-title mrt-journey-wizard__step-title">
              {{ labels.stepDate || 'Välj datum' }}
            </h3>
            <div class="mrt-jw-card mrt-jw-card--calendar mrt-journey-wizard__calendar-card">
              <div class="mrt-jw-calendar__nav mrt-journey-wizard__calendar-nav">
                <button type="button" class="mrt-jw-btn mrt-jw-btn--cal-nav mrt-journey-wizard__cal-prev" aria-label="Previous month">‹</button>
                <span class="mrt-jw-typo mrt-jw-typo--cal-title mrt-journey-wizard__cal-title" aria-live="polite" />
                <button type="button" class="mrt-jw-btn mrt-jw-btn--cal-nav mrt-journey-wizard__cal-next" aria-label="Next month">›</button>
                <button type="button" class="mrt-jw-btn mrt-jw-btn--cal-today mrt-journey-wizard__cal-today" data-wizard-current-month>
                  {{ labels.thisMonth || 'Denna månad' }}
                </button>
              </div>
              <div class="mrt-jw-calendar__grid mrt-journey-wizard__calendar" data-wizard-calendar role="region" />
              <ul class="mrt-jw-calendar__legend mrt-journey-wizard__legend">
                <li><span class="mrt-jw-calendar__swatch mrt-jw-calendar__swatch--ok mrt-journey-wizard__swatch mrt-journey-wizard__swatch--ok" aria-hidden="true" /> {{ labels.legendOk || '' }}</li>
                <li><span class="mrt-jw-calendar__swatch mrt-jw-calendar__swatch--traffic mrt-journey-wizard__swatch mrt-journey-wizard__swatch--traffic" aria-hidden="true" /> {{ labels.legendTraffic || '' }}</li>
                <li><span class="mrt-jw-calendar__swatch mrt-jw-calendar__swatch--none mrt-journey-wizard__swatch mrt-journey-wizard__swatch--none" aria-hidden="true" /> {{ labels.legendNone || '' }}</li>
              </ul>
            </div>
          </div>

          <div class="mrt-jw-panel mrt-journey-wizard__panel" data-wizard-step="outbound" role="region" hidden>
            <header class="mrt-jw-step-head mrt-journey-wizard__step-head">
              <button type="button" class="mrt-jw-btn mrt-jw-btn--back mrt-journey-wizard__back" data-wizard-back="outbound">
                {{ labels.back || '← Tillbaka' }}
              </button>
              <p class="mrt-jw-step-head__context mrt-journey-wizard__context" data-wizard-context />
            </header>
            <h3 class="mrt-jw-typo mrt-jw-typo--step-title mrt-journey-wizard__step-title">
              {{ labels.stepOutbound || 'Välj utresa' }}
            </h3>
            <div data-wizard-outbound />
          </div>

          <div class="mrt-jw-panel mrt-journey-wizard__panel" data-wizard-step="return" role="region" hidden>
            <header class="mrt-jw-step-head mrt-journey-wizard__step-head">
              <button type="button" class="mrt-jw-btn mrt-jw-btn--back mrt-journey-wizard__back" data-wizard-back="return">
                {{ labels.back || '← Tillbaka' }}
              </button>
              <p class="mrt-jw-step-head__context mrt-journey-wizard__context" data-wizard-context />
            </header>
            <div data-wizard-return-summary class="mrt-journey-wizard__selected-trip" />
            <h3 class="mrt-jw-typo mrt-jw-typo--step-title mrt-journey-wizard__step-title">
              {{ labels.stepReturn || 'Välj återresa' }}
            </h3>
            <div data-wizard-return />
          </div>

          <div class="mrt-jw-panel mrt-journey-wizard__panel" data-wizard-step="summary" role="region" hidden>
            <header class="mrt-jw-step-head mrt-journey-wizard__step-head">
              <button type="button" class="mrt-jw-btn mrt-jw-btn--back mrt-journey-wizard__back" data-wizard-back="summary">
                {{ labels.back || '← Tillbaka' }}
              </button>
              <p class="mrt-jw-step-head__context mrt-journey-wizard__context" data-wizard-context />
            </header>
            <h3 class="mrt-jw-typo mrt-jw-typo--step-title mrt-journey-wizard__step-title">
              {{ labels.stepSummary || 'Din resa' }}
            </h3>
            <div data-wizard-summary />
            <p class="mrt-mt-sm" data-wizard-ticket-wrap hidden>
              <a href="#" class="mrt-jw-btn mrt-jw-btn--cta mrt-btn mrt-btn--primary mrt-journey-wizard__cta" data-wizard-ticket>
                {{ labels.ticketCta || 'Fortsätt till biljetter' }}
              </a>
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
