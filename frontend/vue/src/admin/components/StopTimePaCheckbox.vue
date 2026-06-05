<script setup lang="ts">
import { computed } from 'vue';
import { adminConfig } from '../types';
import { adminStr } from '../utils/adminLabels';

const props = defineProps<{
  modelValue: boolean;
  kind: 'pickup' | 'dropoff';
  disabled?: boolean;
  showLabel?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>();

const cfg = adminConfig();

const shortLabel = computed(() =>
  props.kind === 'pickup'
    ? adminStr(cfg, 'stopTimesColPickup')
    : adminStr(cfg, 'stopTimesColDropoff'),
);

const tooltip = computed(() =>
  props.kind === 'pickup'
    ? adminStr(cfg, 'stopTimesPickupLabel')
    : adminStr(cfg, 'stopTimesDropoffLabel'),
);

function onChange(event: Event): void {
  emit('update:modelValue', (event.target as HTMLInputElement).checked);
}
</script>

<template>
  <label class="mrt-stop-pa-check" :title="tooltip">
    <input
      type="checkbox"
      :checked="modelValue"
      :disabled="disabled"
      :aria-label="tooltip"
      @change="onChange"
    />
    <span v-if="showLabel !== false" class="mrt-stop-pa-check__label">{{ shortLabel }}</span>
  </label>
</template>

<style scoped>
.mrt-stop-pa-check {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  cursor: pointer;
}

.mrt-stop-pa-check__label {
  min-width: 1.5rem;
  font-weight: 700;
}
</style>
