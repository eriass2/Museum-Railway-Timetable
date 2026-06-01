<script setup lang="ts">
import { computed } from 'vue';
import type { MonthDayMeta } from '../../config/types';
import { timetableTypeClass } from '../../shared/calendarDay';
import { monthDayButtonAria, monthDayCountTitle, normalizeMonthDayCount } from '../../utils/monthDayLabels';

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

const typeClass = computed(() => timetableTypeClass(props.info.type));

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
  <template v-if="!info.running">
    <span class="mrt-daynum">{{ day }}</span>
  </template>
  <button
    v-else
    type="button"
    class="mrt-day mrt-running mrt-day-clickable mrt-cursor-pointer"
    :class="[{ 'is-selected': selected }, typeClass]"
    :aria-label="buttonAria"
    :aria-pressed="selected"
    :title="countTooltip"
    @click="onClick"
  >
    <span class="mrt-daynum" aria-hidden="true">{{ day }}</span>
  </button>
</template>
