<script setup lang="ts">
import { computed } from 'vue';
import type { StopTimeMode } from '../../types';
import { adminConfig } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import {
  STOP_TIME_MODE_LABEL_KEYS,
  STOP_TIME_MODES,
} from '../../utils/timetable-editor/stopTimeModes';
import { stopTimePaShortLabel } from '../../utils/timetable-editor/stopTimePaLabels';

const props = defineProps<{
  modelValue: StopTimeMode;
  kind: 'pickup' | 'dropoff';
  disabled?: boolean;
}>();

defineEmits<{
  'update:modelValue': [value: StopTimeMode];
}>();

const cfg = adminConfig();

const ariaLabel = computed(() => stopTimePaShortLabel(cfg, props.kind));
</script>

<template>
  <select
    class="mrt-input mrt-stop-time-mode-select"
    :value="modelValue"
    :disabled="disabled"
    :aria-label="ariaLabel"
    @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value as StopTimeMode)"
  >
    <option v-for="mode in STOP_TIME_MODES" :key="mode" :value="mode">
      {{ adminStr(cfg, STOP_TIME_MODE_LABEL_KEYS[mode]) }}
    </option>
  </select>
</template>
