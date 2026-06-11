<script setup lang="ts">
import { computed } from 'vue';
import type { CalendarDayStatus } from '../../shared/calendarDay';

const props = defineProps<{
  day: number;
  ymd: string;
  status: CalendarDayStatus;
  type?: string;
  selected: boolean;
  ariaLabel: string;
}>();

const emit = defineEmits<{ pick: [ymd: string] }>();

const dayClass = computed(() => {
  const base = 'mrt-calendar-day';
  if (props.status === 'ok') {
    return `${base} mrt-calendar-day--ok`;
  }
  if (props.status === 'traffic_no_match') {
    return `${base} mrt-calendar-day--traffic`;
  }
  return `${base} mrt-calendar-day--none`;
});

const isOk = computed(() => props.status === 'ok');

function onClick(): void {
  if (isOk.value) {
    emit('pick', props.ymd);
  }
}
</script>

<template>
  <button
    type="button"
    :class="[dayClass, { 'is-selected': selected && isOk }]"
    :disabled="!isOk"
    :aria-label="ariaLabel"
    :aria-pressed="isOk ? selected : undefined"
    @click="onClick"
  >
    {{ day }}
  </button>
</template>
