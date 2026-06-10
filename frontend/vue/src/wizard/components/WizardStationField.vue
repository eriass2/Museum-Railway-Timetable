<script setup lang="ts">
import { computed } from 'vue';
import MrtCombobox from '../../components/ui/MrtCombobox.vue';
import MrtFieldGroup from '../../components/ui/MrtFieldGroup.vue';
import type { WizardStation } from '../../config/types';

const props = defineProps<{
  id: string;
  label: string;
  placeholder: string;
  searchAria: string;
  stations: WizardStation[];
  modelValue: number;
  excludeId?: number;
}>();

defineEmits<{ 'update:modelValue': [number] }>();

const options = computed(() =>
  props.stations.map((s) => ({ id: s.id, label: s.title })),
);
</script>

<template>
  <MrtFieldGroup :label="label" :input-id="id">
    <MrtCombobox
      :id="id"
      :model-value="modelValue"
      :options="options"
      :placeholder="placeholder"
      :search-aria="searchAria"
      :exclude-id="excludeId"
      @update:model-value="$emit('update:modelValue', $event)"
    />
  </MrtFieldGroup>
</template>
