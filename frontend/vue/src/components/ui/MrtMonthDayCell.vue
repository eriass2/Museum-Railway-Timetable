<script setup lang="ts">
import { computed } from 'vue';
import type { MonthDayMeta } from '../../config/types';
import { timetableTypeBarClass } from '../../shared/calendarDay';
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
    class="mrt-day mrt-running mrt-day-clickable mrt-cursor-pointer"
    :class="{ 'is-selected': selected }"
    :aria-label="buttonAria"
    :aria-pressed="selected"
    :title="countTooltip"
    @click="onClick"
  >
    <span class="mrt-daynum">{{ day }}</span>
    <span v-if="dayTypes.length" class="mrt-day-bars" aria-hidden="true">
      <span
        v-for="t in dayTypes"
        :key="t"
        class="mrt-day-bar"
        :class="timetableTypeBarClass(t)"
      />
    </span>
  </button>
  <span v-else class="mrt-day mrt-day--inactive" aria-hidden="true">
    <span class="mrt-daynum">{{ day }}</span>
  </span>
</template>
