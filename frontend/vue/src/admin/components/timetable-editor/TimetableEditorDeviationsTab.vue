<script setup lang="ts">
import { AdminFormActions, AdminPanel, AdminTrainTypeSelect, AdminUnsavedBanner, MrtButton } from '../ui';
import type { TimetableDetail } from '../../types';
import type { DeviationRow } from '../../utils/deviationsPayload';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  canOperate: boolean;
  deviationsDirty: boolean;
  rows: DeviationRow[];
  trainTypes: TimetableDetail['train_types'];
  trainTypeIconKey: (typeId: number) => string;
}>();

const cfg = adminConfig();
const emit = defineEmits<{ save: [] }>();
</script>

<template>
  <AdminPanel>
    <AdminUnsavedBanner :show="deviationsDirty" :message="adminStr(cfg, 'editorDeviationsUnsaved')" />
    <table class="widefat striped">
      <thead>
        <tr>
          <th>{{ adminStr(cfg, 'editorColDate') }}</th>
          <th>{{ adminStr(cfg, 'editorColTrip') }}</th>
          <th>{{ adminStr(cfg, 'editorColTrainType') }}</th>
          <th>{{ adminStr(cfg, 'editorColMessage') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, idx) in rows" :key="idx">
          <td>{{ row.date }}</td>
          <td>{{ row.trip_label }}</td>
          <td>
            <AdminTrainTypeSelect
              v-model="row.train_type_id"
              show-icon
              :icon-key="trainTypeIconKey(row.train_type_id)"
              :train-types="trainTypes"
              :disabled="!canOperate"
            />
          </td>
          <td>
            <input v-model="row.notice" type="text" class="regular-text" :disabled="!canOperate" />
          </td>
        </tr>
      </tbody>
    </table>
    <AdminFormActions v-if="canOperate">
      <MrtButton context="admin" variant="primary" @click="emit('save')">
        {{ adminStr(cfg, 'editorSaveDeviations') }}
      </MrtButton>
    </AdminFormActions>
  </AdminPanel>
</template>
