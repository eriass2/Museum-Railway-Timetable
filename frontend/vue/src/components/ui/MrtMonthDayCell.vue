<script setup lang="ts">
import { computed } from 'vue';
import type { MonthDayMeta } from '../../config/types';
import {
  timetableTypeMonthBarClass,
  timetableTypeMonthDayClass,
} from '../../shared/calendarDay';
import { monthDayButtonAria, monthDayCountTitle, normalizeMonthDayCount } from '../../utils/monthDayLabels';
import { resolveMonthDayTypes } from '../../utils/monthDayTypes';

const props = defineProps<{
  day: number;
  info: MonthDayMeta;
  showCounts: boolean;
  selected: boolean;
  countTitle?: string;
  runningAria?: string;
  typeLabels?: Record<string, string>;
}>();

const emit = defineEmits<{ click: [ymd: string] }>();

const dayTypes = computed(() => resolveMonthDayTypes(props.info));

const singleTypeClass = computed(() =>
  dayTypes.value.length === 1 ? timetableTypeMonthDayClass(dayTypes.value[0]) : undefined,
);

const countTooltip = computed(() => {
  const count = normalizeMonthDayCount(props.info.count);
  if (!props.showCounts || count <= 0 || !props.countTitle) {
    return undefined;
  }
  return monthDayCountTitle(props.countTitle, count);
});

const buttonAria = computed(() =>
  monthDayButtonAria(props.day, props.info, {
    showCounts: props.showCounts,
    countTitle: props.countTitle,
    runningLabel: props.runningAria,
    typeLabels: props.typeLabels,
  }),
);

function onClick(): void {
  if (props.info.running && props.info.ymd) {
    emit('click', props.info.ymd);
  }
}
</script>

<template>
  <button
    v-if="info.running"
    type="button"
    class="mrt-month-day mrt-running mrt-month-day--clickable mrt-cursor-pointer"
    :class="[
      {
        'is-selected': selected,
        'mrt-month-day--has-types': dayTypes.length > 0,
        'mrt-month-day--multi-types': dayTypes.length > 1,
      },
      singleTypeClass,
    ]"
    :aria-label="buttonAria"
    :aria-pressed="selected"
    :title="countTooltip"
    @click="onClick"
  >
    <span class="mrt-month-day__num">{{ day }}</span>
    <span v-if="dayTypes.length" class="mrt-month-day__bars" aria-hidden="true">
      <span
        v-for="t in dayTypes"
        :key="t"
        class="mrt-month-day__bar"
        :class="timetableTypeMonthBarClass(t)"
      />
    </span>
  </button>
  <span v-else class="mrt-month-day mrt-month-day--inactive" aria-hidden="true">
    <span class="mrt-month-day__num">{{ day }}</span>
  </span>
</template>

<style scoped>
@import './mrtFocusRing.css';
.mrt-month-day {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-content: flex-start;
  position: relative;
  box-sizing: border-box;
  width: 100%;
  min-height: var(--mrt-month-cell-min-height, 5rem);
  height: 100%;
  margin: 0;
  padding: 0.4rem 0.25rem 0;
  border: none;
  text-align: center;
  font: inherit;
  color: inherit;
}

.mrt-month-day--inactive {
  background: var(--mrt-bg-lightest);
  color: var(--mrt-text-tertiary);
  cursor: default;
  justify-content: center;
}

.mrt-month-day.mrt-running {
  background: #fff;
  color: var(--mrt-text-primary, #151515);
}

.mrt-month-day__num {
  flex: 1 1 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 0;
  font-size: var(--mrt-month-day-font, 1.15rem);
  font-weight: 700;
  line-height: 1.1;
}

.mrt-month-day--inactive .mrt-month-day__num {
  opacity: 0.55;
  font-weight: 700;
}

.mrt-month-day__bars {
  display: flex;
  flex-shrink: 0;
  gap: 2px;
  width: 100%;
  height: var(--mrt-month-bar-height, 0.75rem);
  margin-top: auto;
}

.mrt-month-day__bar {
  flex: 1 1 0;
  min-width: 0;
  min-height: var(--mrt-month-bar-height, 0.75rem);
  border-radius: 0;
}

.mrt-month-day--has-types:not(.mrt-month-day--multi-types) .mrt-month-day__bars {
  height: var(--mrt-month-bar-single-height, 1.15rem);
}

.mrt-month-day--has-types:not(.mrt-month-day--multi-types) .mrt-month-day__bar {
  min-height: var(--mrt-month-bar-single-height, 1.15rem);
}

.mrt-month-day__bar--green {
  background: var(--mrt-cal-traffic-green-bg);
}

.mrt-month-day__bar--yellow {
  background: var(--mrt-cal-traffic-yellow-bg);
}

.mrt-month-day__bar--red {
  background: var(--mrt-cal-traffic-red-bg);
}

.mrt-month-day__bar--orange {
  background: var(--mrt-cal-traffic-orange-bg);
}

.mrt-month-day__bar--blue {
  background: var(--mrt-cal-traffic-blue-bg);
}

.mrt-month-day--green {
  background: var(--mrt-cal-traffic-green-bg);
  color: var(--mrt-cal-traffic-green-fg);
}

.mrt-month-day--yellow {
  background: var(--mrt-cal-traffic-yellow-bg);
  color: var(--mrt-cal-traffic-yellow-fg);
}

.mrt-month-day--red {
  background: var(--mrt-cal-traffic-red-bg);
  color: var(--mrt-cal-traffic-red-fg);
}

.mrt-month-day--orange {
  background: var(--mrt-cal-traffic-orange-bg);
  color: var(--mrt-cal-traffic-orange-fg);
}

.mrt-month-day--blue {
  background: var(--mrt-cal-traffic-blue-bg);
  color: var(--mrt-cal-traffic-blue-fg);
}

.mrt-month-day--clickable {
  cursor: pointer;
  transition: box-shadow 0.2s ease, background-color 0.15s ease;
}

.mrt-month-day--clickable:hover {
  box-shadow: inset 0 0 0 2px var(--mrt-border-light);
}

.mrt-month-day--clickable.is-selected {
  box-shadow: inset 0 0 0 3px var(--mrt-green-primary, #296310);
  z-index: 1;
}

@media (prefers-reduced-motion: reduce) {
  .mrt-month-day--clickable {
    transition: none;
  }
}

@media (max-width: 40rem) {
  .mrt-month-day {
    padding: 0.25rem 0.15rem 0;
  }
}
</style>
