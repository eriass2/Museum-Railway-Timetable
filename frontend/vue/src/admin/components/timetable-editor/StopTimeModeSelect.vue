<script setup lang="ts">
import type { StopTimeMode } from '../../types';
import { adminConfig } from '../../types';
import { adminStr } from '../../utils/adminLabels';

defineProps<{
  modelValue: StopTimeMode;
  kind: 'pickup' | 'dropoff';
  disabled?: boolean;
}>();

defineEmits<{
  'update:modelValue': [value: StopTimeMode];
}>();

const cfg = adminConfig();

const options: StopTimeMode[] = ['none', 'scheduled', 'on_request'];
</script>

<template>
  <select
    class="mrt-input mrt-stop-time-mode-select"
    :value="modelValue"
    :disabled="disabled"
    :aria-label="
      kind === 'pickup'
        ? adminStr(cfg, 'stopTimesColPickup')
        : adminStr(cfg, 'stopTimesColDropoff')
    "
    @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value as StopTimeMode)"
  >
    <option value="none">{{ adminStr(cfg, 'stopTimeModeNone') }}</option>
    <option value="scheduled">{{ adminStr(cfg, 'stopTimeModeScheduled') }}</option>
    <option value="on_request">{{ adminStr(cfg, 'stopTimeModeOnRequest') }}</option>
  </select>
</template>
