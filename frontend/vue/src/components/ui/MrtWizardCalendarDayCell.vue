<script setup lang="ts">
import { computed } from 'vue';
import type { CalendarDayStatus } from '../../shared/calendarDay';
import { timetableTypeClass } from '../../shared/calendarDay';

const props = defineProps<{
  day: number;
  ymd: string;
  status: CalendarDayStatus;
  type?: string;
  selected: boolean;
  ariaLabel: string;
}>();

const emit = defineEmits<{ pick: [ymd: string] }>();

const typeClass = computed(() => timetableTypeClass(props.type, 'mrt-calendar-day'));

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
    :class="[{ 'is-selected': selected }, typeClass]"
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
    :class="typeClass"
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
