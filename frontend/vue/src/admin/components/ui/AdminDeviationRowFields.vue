<script setup lang="ts">
import AdminTrainTypeSelect from './AdminTrainTypeSelect.vue';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  trainTypeId: number;
  notice: string;
  trainTypes: { id: number; name: string }[];
  canOperate: boolean;
  meta?: string;
}>();

defineEmits<{
  'update:trainTypeId': [number];
  'update:notice': [string];
}>();

const cfg = adminConfig();
</script>

<template>
  <div class="mrt-admin-deviation-fields">
    <p v-if="meta" class="mrt-admin-mobile-deviation-meta">
      <strong>{{ meta }}</strong>
    </p>
    <p>
      <label>{{ adminStr(cfg, 'editorColTrainType') }}</label>
      <AdminTrainTypeSelect
        :model-value="trainTypeId"
        wide
        :train-types="trainTypes"
        :disabled="!canOperate"
        @update:model-value="$emit('update:trainTypeId', $event)"
      />
    </p>
    <p>
      <label>{{ adminStr(cfg, 'editorColMessage') }}</label>
      <input
        :value="notice"
        type="text"
        class="widefat"
        :disabled="!canOperate"
        @input="$emit('update:notice', ($event.target as HTMLInputElement).value)"
      />
    </p>
  </div>
</template>
