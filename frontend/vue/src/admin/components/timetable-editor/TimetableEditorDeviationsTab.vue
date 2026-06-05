<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import {
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTrainTypeSelect,
  AdminUnsavedBanner,
  MrtButton,
} from '../ui';
import type { TimetableDetail, TimetableServiceRow } from '../types';
import {
  createDeviationRow,
  formatDeviationTripLabel,
  hasDeviationRow,
  type DeviationRow,
} from '../../utils/deviationsPayload';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

const props = defineProps<{
  canOperate: boolean;
  deviationsDirty: boolean;
  services: TimetableServiceRow[];
  dates: string[];
  trainTypes: TimetableDetail['train_types'];
  trainTypeIconKey: (typeId: number) => string;
}>();

const rows = defineModel<DeviationRow[]>('rows', { required: true });

const cfg = adminConfig();
const emit = defineEmits<{ save: [] }>();

const newDate = ref('');
const newServiceId = ref(0);

const canAdd = computed(
  () =>
    props.canOperate &&
    props.dates.length > 0 &&
    props.services.length > 0 &&
    newDate.value !== '' &&
    newServiceId.value > 0 &&
    !hasDeviationRow(rows.value, newServiceId.value, newDate.value),
);

function tripLabel(serviceId: number): string {
  const service = props.services.find((s) => s.id === serviceId);
  return service ? formatDeviationTripLabel(service) : '—';
}

function addDeviation(): void {
  if (!canAdd.value) {
    return;
  }
  const service = props.services.find((s) => s.id === newServiceId.value);
  if (!service || !newDate.value) {
    return;
  }
  rows.value = [...rows.value, createDeviationRow(service, newDate.value)];
}

function removeDeviation(index: number): void {
  rows.value = rows.value.filter((_, i) => i !== index);
}

watch(
  () => [props.dates, props.services] as const,
  ([dates, services]) => {
    if (!newDate.value && dates.length) {
      newDate.value = dates[0];
    }
    if (!newServiceId.value && services.length) {
      newServiceId.value = services[0].id;
    }
  },
  { immediate: true },
);
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
          <th v-if="canOperate"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, idx) in rows" :key="`${row.service_id}-${row.date}-${idx}`">
          <td>{{ row.date }}</td>
          <td>{{ tripLabel(row.service_id) }}</td>
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
          <td v-if="canOperate">
            <AdminRowActions>
              <MrtButton context="admin" variant="link-delete" @click="removeDeviation(idx)">
                {{ adminStr(cfg, 'delete') }}
              </MrtButton>
            </AdminRowActions>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-if="!rows.length" class="description">{{ adminStr(cfg, 'editorDeviationsEmpty') }}</p>
    <div v-if="canOperate" class="mrt-admin-trip-form">
      <select v-model="newDate">
        <option value="">{{ adminStr(cfg, 'editorDeviationDatePrompt') }}</option>
        <option v-for="d in dates" :key="d" :value="d">{{ d }}</option>
      </select>
      <select v-model.number="newServiceId">
        <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
        <option v-for="s in services" :key="s.id" :value="s.id">
          {{ formatDeviationTripLabel(s) }}
        </option>
      </select>
      <div class="mrt-admin-trip-form__actions">
        <MrtButton context="admin" variant="secondary" :disabled="!canAdd" @click="addDeviation">
          {{ adminStr(cfg, 'editorAddDeviation') }}
        </MrtButton>
      </div>
    </div>
    <AdminFormActions v-if="canOperate">
      <MrtButton context="admin" variant="primary" @click="emit('save')">
        {{ adminStr(cfg, 'editorSaveDeviations') }}
      </MrtButton>
    </AdminFormActions>
  </AdminPanel>
</template>
