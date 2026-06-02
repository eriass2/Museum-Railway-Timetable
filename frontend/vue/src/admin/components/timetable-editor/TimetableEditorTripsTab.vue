<script setup lang="ts">
import {
  AdminFormActions,
  AdminInlineField,
  AdminPanel,
  AdminRowActions,
  AdminTrainTypeCell,
  MrtButton,
} from '../ui';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  canManage: boolean;
  detail: TimetableDetail;
  destinations: { id: number; name: string }[];
  trainTypeIconKey: (typeId: number) => string;
}>();

const cfg = adminConfig();
const newTrip = defineModel<{
  route_id: number;
  train_type_id: number;
  end_station_id: number;
}>('newTrip', { required: true });

const emit = defineEmits<{
  'open-stoptimes': [serviceId: number];
  'remove-trip': [serviceId: number];
  'add-trip': [];
}>();
</script>

<template>
  <AdminPanel>
    <table class="widefat striped">
      <thead>
        <tr>
          <th>{{ adminStr(cfg, 'editorColRoute') }}</th>
          <th>{{ adminStr(cfg, 'editorColTrainType') }}</th>
          <th>{{ adminStr(cfg, 'editorColDestination') }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="s in detail.services" :key="s.id">
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
      <select v-model.number="newTrip.route_id">
        <option :value="0">{{ adminStr(cfg, 'editorRoutePrompt') }}</option>
        <option v-for="r in detail.routes" :key="r.id" :value="r.id">{{ r.title }}</option>
      </select>
      <AdminInlineField>
        <AdminTrainTypeCell
          v-if="trainTypeIconKey(newTrip.train_type_id)"
          :icon-key="trainTypeIconKey(newTrip.train_type_id)"
        />
        <select v-model.number="newTrip.train_type_id">
          <option :value="0">{{ adminStr(cfg, 'editorTrainTypePrompt') }}</option>
          <option v-for="t in detail.train_types" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </AdminInlineField>
      <select v-model.number="newTrip.end_station_id">
        <option :value="0">{{ adminStr(cfg, 'editorDestinationPrompt') }}</option>
        <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
      </select>
      <div class="mrt-admin-trip-form__actions">
        <MrtButton context="admin" variant="primary" @click="emit('add-trip')">
          {{ adminStr(cfg, 'editorAddTrip') }}
        </MrtButton>
      </div>
    </div>
  </AdminPanel>
</template>
