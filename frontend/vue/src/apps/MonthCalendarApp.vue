<script setup lang="ts">
import { computed, ref } from 'vue';
import type { MrtVueConfig } from '../useMrtConfig';
import { buildMonthGrid, type MonthDayMeta } from '../utils/monthGrid';
import { mrtPost, msg } from '../api/mrtApi';

const props = defineProps<{ config: MrtVueConfig }>();

const atts = computed(() => (props.config.atts || {}) as Record<string, unknown>);
const showCounts = computed(() => Boolean(atts.value.show_counts));
const showLegend = computed(() => Boolean(atts.value.legend));
const showNav = computed(() => Boolean(atts.value.nav));
const trainType = computed(() => String(atts.value.train_type || ''));

const dates = computed(
  () => (props.config.dates || {}) as Record<number, MonthDayMeta>,
);
const cells = computed(() =>
  buildMonthGrid(
    Number(props.config.daysInMonth) || 0,
    Number(props.config.weekdayFirst) || 1,
    Number(props.config.weekdayFirstSunday) || 0,
    Boolean(props.config.startMonday),
    dates.value,
  ),
);

const cellRows = computed(() => {
  const rows: typeof cells.value[] = [];
  const list = cells.value;
  for (let i = 0; i < list.length; i += 7) {
    rows.push(list.slice(i, i + 7));
  }
  return rows;
});

const selectedYmd = ref('');
const dayHtml = ref('');
const dayLoading = ref(false);
const dayError = ref('');
const panelVisible = ref(false);

const weekdayHeaders = computed(
  () => (props.config.weekdayHeaders as string[]) || [],
);

async function onDayClick(ymd: string) {
  if (!ymd) {
    return;
  }
  selectedYmd.value = ymd;
  panelVisible.value = true;
  dayLoading.value = true;
  dayError.value = '';
  dayHtml.value = '';

  const res = await mrtPost<{ html: string }>(props.config, 'mrt_get_timetable_for_date', {
    date: ymd,
    train_type: trainType.value,
  });

  dayLoading.value = false;
  if (!res.success || !res.data?.html) {
    dayError.value = res.message || msg(props.config, 'errorLoading', 'Error loading timetable.');
    return;
  }
  dayHtml.value = res.data.html;
}
</script>

<template>
  <div
    class="mrt-month mrt-my-1"
    role="region"
    :aria-label="String(config.monthAriaLabel || '')"
    :data-train-type="trainType"
  >
    <div v-if="showNav" class="mrt-month-nav" role="navigation">
      <a class="mrt-btn mrt-btn--secondary mrt-month-nav__prev" :href="String(config.prevMonthUrl || '#')">
        <span class="mrt-month-nav__chev" aria-hidden="true">‹</span>
        {{ String(config.stringsPrevMonth || 'Previous month') }}
      </a>
      <h2 class="mrt-month-nav__title mrt-heading mrt-heading--lg mrt-font-semibold">
        {{ String(config.monthTitle || '') }}
      </h2>
      <a class="mrt-btn mrt-btn--secondary mrt-month-nav__next" :href="String(config.nextMonthUrl || '#')">
        {{ String(config.stringsNextMonth || 'Next month') }}
        <span class="mrt-month-nav__chev" aria-hidden="true">›</span>
      </a>
    </div>
    <div v-else class="mrt-heading mrt-heading--lg mrt-font-semibold">
      {{ String(config.monthTitle || '') }}
    </div>

    <table class="mrt-month-table">
      <caption class="mrt-month-table__caption">{{ String(config.tableCaption || '') }}</caption>
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
                @click="onDayClick(cell.info.ymd)"
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
        {{ String(config.legendServiceDay || '') }}
      </span>
      <span v-if="showCounts" class="mrt-text-small mrt-opacity-85">
        ({{ String(config.legendCountHint || '') }})
      </span>
      <span class="mrt-text-tertiary mrt-text-small">
        ({{ String(config.legendClickHint || '') }})
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
        {{ msg(config, 'loading', 'Loading...') }}
      </p>
      <div v-else-if="dayError" class="mrt-alert mrt-alert-error" role="alert">{{ dayError }}</div>
      <div v-else v-html="dayHtml" />
    </div>
  </div>
</template>
