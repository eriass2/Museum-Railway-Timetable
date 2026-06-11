<script setup lang="ts">
import type { DeviationRow } from '../../utils/timetable-editor/deviationsPayload';
import {
  deviationNoticePreview,
  deviationRowIsCancelled,
  deviationTrainTypeName,
  deviationTripLabelForId,
} from '../../utils/timetable-editor/deviationRowDisplay';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { AdminFormActions, AdminRowActions, MrtButton } from '../ui';

defineProps<{
  rows: DeviationRow[];
  canOperate: boolean;
  services: TimetableDetail['services'];
  trainTypes: TimetableDetail['train_types'];
  cancelledNotice: string;
}>();

const emit = defineEmits<{
  'start-create': [];
  'start-edit': [index: number];
  remove: [index: number];
}>();

const cfg = adminConfig();

function tripLabel(serviceId: number, services: TimetableDetail['services']): string {
  return deviationTripLabelForId(services, serviceId);
}

function trainTypeName(typeId: number, trainTypes: TimetableDetail['train_types']): string {
  return deviationTrainTypeName(trainTypes, typeId);
}

function rowIsCancelled(row: DeviationRow, cancelledNotice: string): boolean {
  return deviationRowIsCancelled(row, cancelledNotice);
}
</script>

<template>
  <p class="description">{{ adminStr(cfg, 'editorDeviationsIntro') }}</p>
  <p class="description">{{ adminStr(cfg, 'editorDeviationsBatchHint') }}</p>
  <table v-if="rows.length" class="widefat striped">
    <thead>
      <tr>
        <th>{{ adminStr(cfg, 'editorColDate') }}</th>
        <th>{{ adminStr(cfg, 'editorColTrip') }}</th>
        <th>{{ adminStr(cfg, 'editorColTrainType') }}</th>
        <th>{{ adminStr(cfg, 'editorDeviationCancelled') }}</th>
        <th>{{ adminStr(cfg, 'editorColMessage') }}</th>
        <th v-if="canOperate"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(row, idx) in rows" :key="`${row.service_id}-${row.date}-${idx}`">
        <td>{{ row.date }}</td>
        <td>{{ tripLabel(row.service_id, services) }}</td>
        <td>{{ trainTypeName(row.train_type_id, trainTypes) }}</td>
        <td>{{ rowIsCancelled(row, cancelledNotice) ? adminStr(cfg, 'yes') : '—' }}</td>
        <td>{{ deviationNoticePreview(row.notice) }}</td>
        <td v-if="canOperate">
          <AdminRowActions>
            <MrtButton context="admin" variant="secondary" @click="emit('start-edit', idx)">
              {{ adminStr(cfg, 'edit') }}
            </MrtButton>
            <MrtButton context="admin" variant="link-delete" @click="emit('remove', idx)">
              {{ adminStr(cfg, 'delete') }}
            </MrtButton>
          </AdminRowActions>
        </td>
      </tr>
    </tbody>
  </table>
  <p v-else class="description">{{ adminStr(cfg, 'editorDeviationsEmpty') }}</p>
  <AdminFormActions v-if="canOperate">
    <MrtButton context="admin" variant="primary" @click="emit('start-create')">
      {{ adminStr(cfg, 'editorAddDeviation') }}
    </MrtButton>
  </AdminFormActions>
</template>
