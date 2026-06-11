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

const emit = defineEmits<{ 'update:modelValue': [number] }>();

const cfg = adminConfig();

function onChange(event: Event) {
  emit('update:modelValue', Number((event.target as HTMLSelectElement).value));
}
</script>

<template>
  <AdminInlineField
    class="admin-train-type-select"
    :class="{ 'admin-train-type-select--with-icon': showIcon }"
  >
    <TrainTypeIcon
      v-if="showIcon && iconKey"
      :icon-key="iconKey"
      :label="iconLabel || iconKey"
    />
    <select
      :id="selectId"
      :class="wide ? 'widefat' : undefined"
      :value="modelValue"
      :disabled="disabled"
      @change="onChange"
    >
      <option :value="0">{{ adminStr(cfg, props.emptyLabelKey) }}</option>
      <option v-for="t in trainTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
    </select>
  </AdminInlineField>
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
