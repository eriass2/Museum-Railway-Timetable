<script setup lang="ts">
import { AdminFormActions, AdminInlineField, AdminPanel, AdminTrainTypeCell, MrtButton } from '../ui';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

export type DeviationRow = {
  service_id: number;
  date: string;
  trip_label: string;
  train_type_id: number;
  notice: string;
};

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
    <p v-if="deviationsDirty" class="notice notice-warning mrt-admin-unsaved">
      {{ adminStr(cfg, 'editorDeviationsUnsaved') }}
    </p>
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
            <AdminInlineField>
              <AdminTrainTypeCell
                v-if="trainTypeIconKey(row.train_type_id)"
                :icon-key="trainTypeIconKey(row.train_type_id)"
              />
              <select v-model.number="row.train_type_id" :disabled="!canOperate">
                <option :value="0">{{ adminStr(cfg, 'editorStandardTrainType') }}</option>
                <option v-for="t in trainTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
            </AdminInlineField>
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
