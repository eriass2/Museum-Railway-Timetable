<script setup lang="ts">
import { computed } from 'vue';
import { trainTypeIconLabel } from '../../../shared/trainTypeIcons';
import TrainTypeIcon from '../TrainTypeIcon.vue';

const props = withDefaults(
  defineProps<{
    modelValue: string;
    iconKeys: string[];
    disabled?: boolean;
    compact?: boolean;
    ariaLabel?: string;
  }>(),
  {
    disabled: false,
    compact: false,
    ariaLabel: 'Välj ikon för tågtyp',
  },
);

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const options = computed(() =>
  props.iconKeys.map((key) => ({
    key,
    label: trainTypeIconLabel(key),
  })),
);

function select(key: string) {
  if (props.disabled) {
    return;
  }
  emit('update:modelValue', key);
}
</script>

<template>
  <div
    class="train-type-icon-picker"
    :class="{ 'train-type-icon-picker--compact': compact }"
    role="radiogroup"
    :aria-label="ariaLabel"
  >
    <button
      v-for="opt in options"
      :key="opt.key"
      type="button"
      class="train-type-icon-picker__option"
      :class="{ 'train-type-icon-picker__option--active': modelValue === opt.key }"
      role="radio"
      :aria-checked="modelValue === opt.key"
      :aria-label="opt.label"
      :disabled="disabled"
      @click="select(opt.key)"
    >
      <TrainTypeIcon :icon-key="opt.key" :label="opt.label" :size="compact ? 24 : 32" />
      <span class="train-type-icon-picker__label">{{ opt.label }}</span>
    </button>
  </div>
</template>

<style scoped>
.train-type-icon-picker {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.train-type-icon-picker__option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  min-width: 4.75rem;
  padding: 8px 6px;
  border: 1px solid #c3c4c7;
  border-radius: 3px;
  background: #fff;
  color: #1d2327;
  cursor: pointer;
  transition:
    border-color 0.15s ease,
    background-color 0.15s ease,
    box-shadow 0.15s ease;
}

.train-type-icon-picker__option:hover:not(:disabled),
.train-type-icon-picker__option:focus-visible {
  border-color: #2271b1;
  background: #f6f7f7;
  outline: none;
  box-shadow: 0 0 0 1px #2271b1;
}

.train-type-icon-picker__option--active {
  border-color: #2271b1;
  background: #f0f6fc;
  box-shadow: inset 0 0 0 1px #2271b1;
}

.train-type-icon-picker__option:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.train-type-icon-picker__label {
  font-size: 12px;
  line-height: 1.2;
  text-align: center;
}

.train-type-icon-picker--compact {
  gap: 6px;
}

.train-type-icon-picker--compact .train-type-icon-picker__option {
  min-width: 3.75rem;
  padding: 6px 4px;
}

.train-type-icon-picker--compact .train-type-icon-picker__label {
  font-size: 11px;
}
</style>
