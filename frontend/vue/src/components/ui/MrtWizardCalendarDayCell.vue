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

<style scoped>
.mrt-calendar-day {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  max-width: 2.65rem;
  min-width: 2rem;
  min-height: 2.65rem;
  margin: 0 auto;
  border: 0;
  border-radius: 0;
  background: transparent;
  color: var(--mrt-color-neutral-600);
  font-size: 1.15rem;
  line-height: 1;
  font-weight: 700;
  text-transform: none;
  padding: 0;
  box-sizing: border-box;
}

.mrt-calendar-day--ok {
  background: var(--mrt-cal-traffic-yellow-bg, var(--mrt-wizard-yellow));
  color: var(--mrt-cal-traffic-yellow-fg, var(--mrt-color-on-accent));
}

.mrt-calendar-day--ok:hover {
  filter: brightness(0.94);
}

.mrt-calendar-day--traffic {
  background: var(--mrt-cal-traffic-muted-bg);
  color: var(--mrt-cal-traffic-muted-fg);
  border: 1px solid var(--mrt-color-neutral-400, #999);
  cursor: not-allowed;
}

.mrt-calendar-day--none {
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-color-neutral-500);
  cursor: not-allowed;
}

.mrt-calendar-day.is-selected,
.mrt-calendar-day--ok.is-selected {
  box-shadow: inset 0 0 0 3px var(--mrt-color-neutral-900);
}
</style>
