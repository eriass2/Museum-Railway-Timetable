<script setup lang="ts">
import { computed, ref } from 'vue';
import MrtCalendarNav from '../components/ui/MrtCalendarNav.vue';
import MrtHtmlPanel from '../components/ui/MrtHtmlPanel.vue';
import MrtLegend from '../components/ui/MrtLegend.vue';
import type { MonthVueConfig } from '../config/types';
import { buildMonthGrid } from '../utils/monthGrid';
import { chunkWeekRows } from '../utils/calendarGrid';
import { useTimetableHtml } from '../composables/useTimetableHtml';
import { resolveMrtString } from '../utils/mrtStrings';

const props = defineProps<{ config: MonthVueConfig }>();

const atts = computed(() => props.config.atts || {});
const showCounts = computed(() => Boolean(atts.value.show_counts));
const showLegend = computed(() => Boolean(atts.value.legend));
const showNav = computed(() => Boolean(atts.value.nav));
const trainType = computed(() => String(atts.value.train_type || ''));

const dates = computed(() => props.config.dates || {});
const cells = computed(() =>
  buildMonthGrid(
    Number(props.config.daysInMonth) || 0,
    Number(props.config.weekdayFirst) || 1,
    Number(props.config.weekdayFirstSunday) || 0,
    Boolean(props.config.startMonday),
    dates.value,
  ),
);

const cellRows = computed(() => chunkWeekRows(cells.value));

const selectedYmd = ref('');
const panelVisible = ref(false);

const { html: dayHtml, loading: dayLoading, error: dayError, fetchDayHtml } =
  useTimetableHtml(props.config);

const weekdayHeaders = computed(() => props.config.weekdayHeaders || []);

const legendItems = computed(() => [
  {
    dotClass: 'mrt-dot--green',
    label: props.config.legendServiceDay || '',
  },
]);

async function onDayClick(ymd: string): Promise<void> {
  if (!ymd) {
    return;
  }
  selectedYmd.value = ymd;
  panelVisible.value = true;
  await fetchDayHtml(ymd, trainType.value);
}
</script>

<template>
  <div
    class="mrt-month mrt-my-1"
    role="region"
    :aria-label="config.monthAriaLabel || ''"
    :data-train-type="trainType"
  >
    <MrtCalendarNav
      v-if="showNav"
      mode="links"
      :month-title="config.monthTitle || ''"
      :prev-href="config.prevMonthUrl || '#'"
      :next-href="config.nextMonthUrl || '#'"
      :prev-text="config.stringsPrevMonth || 'Föregående månad'"
      :next-text="config.stringsNextMonth || 'Nästa månad'"
    />
    <h2 v-else class="mrt-heading mrt-heading--lg mrt-font-semibold">
      {{ config.monthTitle || '' }}
    </h2>

    <table class="mrt-month-table">
      <caption class="mrt-month-table__caption">{{ config.tableCaption || '' }}</caption>
      <thead>
        <tr>
          <th v-for="(h, i) in weekdayHeaders" :key="i" scope="col">{{ h }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, ri) in cellRows" :key="ri">
          <template v-for="(cell, ci) in row" :key="`${ri}-${ci}`">
            <td v-if="cell.kind === 'empty'" class="mrt-empty" />
            <td
              v-else-if="cell.kind === 'day' && !cell.info.running"
              class="mrt-day-cell mrt-day-cell--inactive"
            >
              <span class="mrt-daynum">{{ cell.day }}</span>
            </td>
            <td v-else-if="cell.kind === 'day'" class="mrt-day-cell mrt-day-cell--running">
              <button
                type="button"
                class="mrt-day mrt-running mrt-day-clickable mrt-cursor-pointer"
                :class="{ 'mrt-day-active': selectedYmd === cell.info.ymd }"
                :aria-pressed="selectedYmd === cell.info.ymd"
                @click="onDayClick(cell.info.ymd || '')"
              >
                <span class="mrt-daynum" aria-hidden="true">{{ cell.day }}</span>
                <span class="mrt-dot" aria-hidden="true">{{
                  showCounts ? cell.info.count : '•'
                }}</span>
              </button>
            </td>
          </template>
        </tr>
      </tbody>
    </table>

    <div v-if="showLegend" class="mrt-mt-sm">
      <MrtLegend :items="legendItems" />
      <span v-if="showCounts" class="mrt-text-small mrt-opacity-85 mrt-ml-sm">
        ({{ config.legendCountHint || '' }})
      </span>
      <span class="mrt-text-tertiary mrt-text-small">
        ({{ config.legendClickHint || '' }})
      </span>
    </div>

    <MrtHtmlPanel
      :visible="panelVisible"
      surface
      box
      :loading="dayLoading"
      :error="dayError"
      :loading-text="resolveMrtString(config, 'loading', 'Laddar...')"
    >
      <!-- Trusted server HTML — see frontend/vue/TRUSTED_HTML.md -->
      <div v-html="dayHtml" />
    </MrtHtmlPanel>
  </div>
</template>
