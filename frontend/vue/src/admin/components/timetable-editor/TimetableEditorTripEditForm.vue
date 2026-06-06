<script setup lang="ts">
import {
  AdminFormActions,
  AdminPanel,
  AdminTrainTypeSelect,
  MrtButton,
} from '../ui';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

export type TripEditDraft = {
  service_id: number;
  service_number: string;
  route_id: number;
  train_type_id: number;
  end_station_id: number;
};

defineProps<{
  detail: TimetableDetail;
  destinations: { id: number; name: string }[];
  trainTypeIconKey: (typeId: number) => string;
}>();

const draft = defineModel<TripEditDraft>('draft', { required: true });

const cfg = adminConfig();

const emit = defineEmits<{
  save: [];
  cancel: [];
  'route-change': [];
}>();
</script>

<template>
  <AdminPanel class="mrt-admin-trip-edit">
    <h3 class="mrt-admin-trip-edit__title">{{ adminStr(cfg, 'editorEditTripTitle') }}</h3>
    <div class="mrt-admin-trip-form">
      <p>
        <label :for="`mrt-trip-num-${draft.service_id}`">
          {{ adminStr(cfg, 'editorColServiceNumber') }}
        </label>
        <input
          :id="`mrt-trip-num-${draft.service_id}`"
          v-model="draft.service_number"
          type="text"
          class="regular-text"
        />
        <span class="description">{{ adminStr(cfg, 'editorServiceNumberHint') }}</span>
      </p>
      <select v-model.number="draft.route_id" @change="emit('route-change')">
        <option :value="0">{{ adminStr(cfg, 'editorRoutePrompt') }}</option>
        <option v-for="r in detail.routes" :key="r.id" :value="r.id">{{ r.title }}</option>
      </select>
      <AdminTrainTypeSelect
        v-model="draft.train_type_id"
        :train-types="detail.train_types"
        :icon-key="trainTypeIconKey(draft.train_type_id)"
        show-icon
        empty-label-key="editorTrainTypePrompt"
      />
      <select v-model.number="draft.end_station_id">
        <option :value="0">{{ adminStr(cfg, 'editorDestinationPrompt') }}</option>
        <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
      </select>
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="emit('save')">
          {{ adminStr(cfg, 'editorSaveTrip') }}
        </MrtButton>
        <MrtButton context="admin" variant="secondary" @click="emit('cancel')">
          {{ adminStr(cfg, 'editorCancelEdit') }}
        </MrtButton>
      </AdminFormActions>
    </div>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-trip-edit {
  margin-top: 12px;
  border: 1px solid #c3c4c7;
}

.mrt-admin-trip-edit__title {
  margin: 0 0 8px;
  font-size: 14px;
}
</style>
