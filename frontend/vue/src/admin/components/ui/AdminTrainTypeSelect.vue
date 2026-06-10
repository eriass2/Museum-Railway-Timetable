<script setup lang="ts">
import AdminInlineField from './AdminInlineField.vue';
import TrainTypeIcon from '../TrainTypeIcon.vue';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

const props = withDefaults(
  defineProps<{
    modelValue: number;
    trainTypes: { id: number; name: string }[];
    disabled?: boolean;
    showIcon?: boolean;
    iconKey?: string;
    iconLabel?: string;
    wide?: boolean;
    emptyLabelKey?: string;
    selectId?: string;
  }>(),
  { showIcon: false, wide: false, emptyLabelKey: 'editorStandardTrainType', iconLabel: '' },
);

defineEmits<{ 'update:modelValue': [number] }>();

const cfg = adminConfig();
</script>

<template>
  <AdminInlineField v-if="showIcon" class="admin-train-type-select--with-icon">
    <TrainTypeIcon
      v-if="iconKey"
      :icon-key="iconKey"
      :label="iconLabel || iconKey"
    />
    <select
      :id="selectId"
      :class="wide ? 'widefat' : undefined"
      :value="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', Number(($event.target as HTMLSelectElement).value))"
    >
      <option :value="0">{{ adminStr(cfg, props.emptyLabelKey) }}</option>
      <option v-for="t in trainTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
    </select>
  </AdminInlineField>
  <select
    v-else
    :id="selectId"
    :class="wide ? 'widefat' : undefined"
    :value="modelValue"
    :disabled="disabled"
    @change="$emit('update:modelValue', Number(($event.target as HTMLSelectElement).value))"
  >
    <option :value="0">{{ adminStr(cfg, props.emptyLabelKey) }}</option>
    <option v-for="t in trainTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
  </select>
</template>

<style scoped>
.admin-train-type-select--with-icon {
  width: 100%;
}

.admin-train-type-select--with-icon :deep(select) {
  flex: 1;
  min-width: 0;
}
</style>
