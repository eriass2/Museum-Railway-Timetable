<script setup lang="ts">
import { computed } from 'vue';
import AdminTrainTypeSelect from './AdminTrainTypeSelect.vue';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { isCancelledDeviationNotice, toggleCancelledDeviationNotice } from '../../utils/deviationsPayload';

const props = defineProps<{
  trainTypeId: number;
  notice: string;
  trainTypes: { id: number; name: string }[];
  canOperate: boolean;
  meta?: string;
}>();

const emit = defineEmits<{
  'update:trainTypeId': [number];
  'update:notice': [string];
}>();

const cfg = adminConfig();
const cancelledNotice = computed(() => adminStr(cfg, 'trafficCancelledNotice'));

const isCancelled = computed({
  get: () => isCancelledDeviationNotice(props.notice, cancelledNotice.value),
  set: (value: boolean) => {
    emit('update:notice', toggleCancelledDeviationNotice(props.notice, value, cancelledNotice.value));
  },
});
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
      <label>
        <input
          v-model="isCancelled"
          type="checkbox"
          :disabled="!canOperate"
        />
        {{ adminStr(cfg, 'editorDeviationCancelled') }}
      </label>
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
