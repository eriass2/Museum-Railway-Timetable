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
