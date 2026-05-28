<script setup lang="ts">
export type WizardCalendarDayStatus = 'ok' | 'traffic_no_match' | 'none';

const props = defineProps<{
  day: number;
  ymd: string;
  status: WizardCalendarDayStatus;
  selected: boolean;
  ariaLabel: string;
}>();

const emit = defineEmits<{ pick: [ymd: string] }>();

function onClick(): void {
  if (props.status === 'ok') {
    emit('pick', props.ymd);
  }
}
</script>

<template>
  <button
    v-if="status === 'ok'"
    type="button"
    class="mrt-calendar-day mrt-calendar-day--ok"
    :class="{ 'is-selected': selected }"
    :aria-label="ariaLabel"
    :aria-pressed="selected"
    @click="onClick"
  >
    {{ day }}
  </button>
  <button
    v-else-if="status === 'traffic_no_match'"
    type="button"
    class="mrt-calendar-day mrt-calendar-day--traffic"
    disabled
    :aria-label="ariaLabel"
  >
    {{ day }}
  </button>
  <button
    v-else
    type="button"
    class="mrt-calendar-day mrt-calendar-day--none"
    disabled
    :aria-label="ariaLabel"
  >
    {{ day }}
  </button>
</template>
