<script setup lang="ts">
import {
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTrainTypeCell,
  MrtButton,
} from '../ui';
import TimetableTripFieldsBlock from './TimetableTripFieldsBlock.vue';
import type { TimetableTripDraft } from './tripFormTypes';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  canManage: boolean;
  detail: TimetableDetail;
  destinations: { id: number; name: string }[];
  editingTripId: number;
  trainTypeIconKey: (typeId: number) => string;
}>();

const cfg = adminConfig();
const newTrip = defineModel<TimetableTripDraft>('newTrip', { required: true });

const emit = defineEmits<{
  'open-stoptimes': [serviceId: number];
  'start-edit': [serviceId: number];
  'remove-trip': [serviceId: number];
  'add-trip': [];
}>();
</script>

<template>
  <AdminPanel>
    <table class="widefat striped">
      <thead>
        <tr>
          <th>{{ adminStr(cfg, 'editorColTrip') }}</th>
          <th>{{ adminStr(cfg, 'editorColRoute') }}</th>
          <th>{{ adminStr(cfg, 'editorColTrainType') }}</th>
          <th>{{ adminStr(cfg, 'editorColDestination') }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="s in detail.services" :key="s.id">
          <td>{{ s.service_number }}</td>
          <td>{{ s.route_name }}</td>
          <td>
            <AdminTrainTypeCell
              :icon-key="s.train_type_icon_key"
              :name="s.train_type_name"
            />
          </td>
          <td>{{ s.destination || '—' }}</td>
          <td>
            <AdminRowActions>
              <MrtButton
                v-if="canManage"
                context="admin"
                variant="secondary"
                :disabled="editingTripId > 0 && editingTripId !== s.id"
                @click="emit('start-edit', s.id)"
              >
                {{ adminStr(cfg, 'editorEditTrip') }}
              </MrtButton>
              <MrtButton context="admin" variant="secondary" @click="emit('open-stoptimes', s.id)">
                {{ adminStr(cfg, 'editorStopptimes') }}
              </MrtButton>
              <MrtButton
                v-if="canManage"
                context="admin"
                variant="link-delete"
                @click="emit('remove-trip', s.id)"
              >
                {{ adminStr(cfg, 'delete') }}
              </MrtButton>
            </AdminRowActions>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="canManage" class="mrt-admin-trip-form">
      <h3 class="mrt-admin-trip-form__title">{{ adminStr(cfg, 'editorAddTrip') }}</h3>
      <TimetableTripFieldsBlock
        v-model:draft="newTrip"
        :detail="detail"
        :destinations="destinations"
        field-id-prefix="trip-new"
        :train-type-icon-key="trainTypeIconKey"
      />
      <div class="mrt-admin-trip-form__actions">
        <MrtButton context="admin" variant="primary" @click="emit('add-trip')">
          {{ adminStr(cfg, 'editorAddTrip') }}
        </MrtButton>
      </div>
    </div>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-trip-form__title {
  margin: 12px 0 8px;
  font-size: 14px;
}
</style>
