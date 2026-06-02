<script setup lang="ts">
import AdminInlineField from './AdminInlineField.vue';
import AdminTrainTypeCell from './AdminTrainTypeCell.vue';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

const props = withDefaults(
  defineProps<{
    modelValue: number;
    trainTypes: { id: number; name: string }[];
    disabled?: boolean;
    showIcon?: boolean;
    iconKey?: string;
    wide?: boolean;
  }>(),
  { showIcon: false, wide: false },
);

defineEmits<{ 'update:modelValue': [number] }>();

const cfg = adminConfig();
</script>

<template>
  <AdminInlineField v-if="showIcon">
    <AdminTrainTypeCell v-if="iconKey" :icon-key="iconKey" />
    <select
      :class="wide ? 'widefat' : undefined"
      :value="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', Number(($event.target as HTMLSelectElement).value))"
    >
      <option :value="0">{{ adminStr(cfg, 'editorStandardTrainType') }}</option>
      <option v-for="t in trainTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
    </select>
  </AdminInlineField>
  <select
    v-else
    :class="wide ? 'widefat' : undefined"
    :value="modelValue"
    :disabled="disabled"
    @change="$emit('update:modelValue', Number(($event.target as HTMLSelectElement).value))"
  >
    <option :value="0">{{ adminStr(cfg, 'editorStandardTrainType') }}</option>
    <option v-for="t in trainTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
  </select>
</template>
