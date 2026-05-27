<script setup lang="ts">
import { computed, ref } from 'vue';
import type { MonthDayMeta, MonthVueConfig } from '../config/types';
import { buildMonthGrid } from '../utils/monthGrid';
import { chunkWeekRows } from '../utils/calendarGrid';
import { useMrtAjax } from '../composables/useMrtAjax';
import { msg } from '../api/mrtApi';

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
const dayHtml = ref('');
const panelVisible = ref(false);

const { loading: dayLoading, error: dayError, run } = useMrtAjax(props.config);

const weekdayHeaders = computed(() => props.config.weekdayHeaders || []);

async function onDayClick(ymd: string) {
  if (!ymd) {
    return;
  }
  selectedYmd.value = ymd;
  panelVisible.value = true;
  dayHtml.value = '';

  const res = await run<{ html: string }>('mrt_get_timetable_for_date', {
    date: ymd,
    train_type: trainType.value,
  });

  if (res.success && res.data?.html) {
    dayHtml.value = res.data.html;
  }
}
</script>

<template>
  <div
    class="mrt-month mrt-my-1"
    role="region"
    :aria-label="config.monthAriaLabel || ''"
    :data-train-type="trainType"
  >
    <div v-if="showNav" class="mrt-month-nav" role="navigation">
      <a class="mrt-btn mrt-btn--secondary mrt-month-nav__prev" :href="config.prevMonthUrl || '#'">
        <span class="mrt-month-nav__chev" aria-hidden="true">‹</span>
        {{ config.stringsPrevMonth || 'Föregående månad' }}
      </a>
      <h2 class="mrt-month-nav__title mrt-heading mrt-heading--lg mrt-font-semibold">
        {{ config.monthTitle || '' }}
      </h2>
      <a class="mrt-btn mrt-btn--secondary mrt-month-nav__next" :href="config.nextMonthUrl || '#'">
        {{ config.stringsNextMonth || 'Nästa månad' }}
        <span class="mrt-month-nav__chev" aria-hidden="true">›</span>
      </a>
    </div>
    <div v-else class="mrt-heading mrt-heading--lg mrt-font-semibold">
      {{ config.monthTitle || '' }}
    </div>

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

    <div v-if="showLegend" class="mrt-legend mrt-text-base mrt-text-primary mrt-mt-sm">
      <span class="mrt-legend-item mrt-inline-flex mrt-items-center mrt-gap-xs mrt-mr-sm">
        <span class="mrt-dot mrt-dot--green" aria-hidden="true" />
        {{ config.legendServiceDay || '' }}
      </span>
      <span v-if="showCounts" class="mrt-text-small mrt-opacity-85">
        ({{ config.legendCountHint || '' }})
      </span>
      <span class="mrt-text-tertiary mrt-text-small">
        ({{ config.legendClickHint || '' }})
      </span>
    </div>

    <div
      class="mrt-box mrt-day-timetable-container mrt-mt-xl"
      :class="{ 'mrt-hidden': !panelVisible }"
      role="region"
      aria-live="polite"
      tabindex="-1"
    >
      <p v-if="dayLoading" class="mrt-empty mrt-empty--loading">
        {{ msg(config, 'loading', 'Laddar...') }}
      </p>
      <div v-else-if="dayError" class="mrt-alert mrt-alert-error" role="alert">{{ dayError }}</div>
      <div v-else v-html="dayHtml" />
    </div>
  </div>
</template>
