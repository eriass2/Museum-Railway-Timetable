<script setup lang="ts">
import type { TimetableDetail } from '../../types';
import { formatTripLineDisplay } from '../../utils/timetable-editor/tripLineDisplay';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { AdminFormActions, AdminRowActions, AdminTrainTypeCell, MrtButton } from '../ui';

defineProps<{
  canManage: boolean;
  detail: TimetableDetail;
}>();

const emit = defineEmits<{
  'start-create': [];
  'start-edit': [serviceId: number];
  'open-stoptimes': [serviceId: number];
  'remove-trip': [serviceId: number];
}>();

const cfg = adminConfig();
</script>

<template>
  <table class="widefat striped">
    <thead>
      <tr>
        <th>{{ adminStr(cfg, 'editorColTrip') }}</th>
        <th>{{ adminStr(cfg, 'editorColLine') }}</th>
        <th>{{ adminStr(cfg, 'editorColTrainType') }}</th>
        <th>{{ adminStr(cfg, 'editorColDestination') }}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="s in detail.services" :key="s.id">
        <td>{{ s.service_number }}</td>
        <td>{{ formatTripLineDisplay(s.line_name, s.route_name) }}</td>
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
  <AdminFormActions v-if="canManage">
    <MrtButton context="admin" variant="primary" @click="emit('start-create')">
      {{ adminStr(cfg, 'editorAddTrip') }}
    </MrtButton>
  </AdminFormActions>
</template>
