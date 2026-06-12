<script setup lang="ts">
import type { DeviationRow } from '../../utils/timetable-editor/deviationsPayload';
import { formatDeviationTripLabel } from '../../utils/timetable-editor/deviationsPayload';
import { deviationTripLabelForId } from '../../utils/timetable-editor/deviationRowDisplay';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import {
  AdminBackNav,
  AdminFieldStack,
  AdminFormActions,
  AdminTrainTypeSelect,
  MrtButton,
} from '../ui';

defineProps<{
  viewMode: 'create' | 'edit';
  draft: DeviationRow;
  canOperate: boolean;
  services: TimetableDetail['services'];
  dates: string[];
  trainTypes: TimetableDetail['train_types'];
  trainTypeIconKey: (typeId: number) => string;
  canApplyCreate: boolean;
  draftIsCancelled: () => boolean;
}>();

const emit = defineEmits<{
  back: [];
  apply: [];
  'update-date': [date: string];
  'update-trip': [serviceId: number];
  'update-cancelled': [checked: boolean];
}>();

const cfg = adminConfig();

function tripLabel(serviceId: number, services: TimetableDetail['services']): string {
  return deviationTripLabelForId(services, serviceId);
}
</script>

<template>
  <AdminBackNav @back="emit('back')" />
  <h3 class="mrt-admin-deviation-detail__title">
    {{
      viewMode === 'create'
        ? adminStr(cfg, 'editorAddDeviation')
        : adminStr(cfg, 'editorEditDeviation')
    }}
  </h3>
  <div class="mrt-admin-trip-fields">
    <AdminFieldStack
      v-if="viewMode === 'create'"
      :label="adminStr(cfg, 'editorColDate')"
      label-for="mrt-deviation-date"
    >
      <select
        id="mrt-deviation-date"
        class="widefat"
        :value="draft.date"
        @change="emit('update-date', ($event.target as HTMLSelectElement).value)"
      >
        <option value="">{{ adminStr(cfg, 'editorDeviationDatePrompt') }}</option>
        <option v-for="d in dates" :key="d" :value="d">{{ d }}</option>
      </select>
    </AdminFieldStack>
    <p v-else class="mrt-admin-trip-fields__field">
      <strong>{{ adminStr(cfg, 'editorColDate') }}:</strong> {{ draft.date }}
    </p>
    <AdminFieldStack
      v-if="viewMode === 'create'"
      :label="adminStr(cfg, 'editorColTrip')"
      label-for="mrt-deviation-trip"
    >
      <select
        id="mrt-deviation-trip"
        class="widefat"
        :value="draft.service_id"
        @change="emit('update-trip', Number(($event.target as HTMLSelectElement).value))"
      >
        <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
        <option v-for="s in services" :key="s.id" :value="s.id">
          {{ formatDeviationTripLabel(s) }}
        </option>
      </select>
    </AdminFieldStack>
    <p v-else class="mrt-admin-trip-fields__field">
      <strong>{{ adminStr(cfg, 'editorColTrip') }}:</strong> {{ tripLabel(draft.service_id, services) }}
    </p>
    <AdminFieldStack :label="adminStr(cfg, 'editorColTrainType')">
      <AdminTrainTypeSelect
        v-model="draft.train_type_id"
        show-icon
        wide
        :icon-key="trainTypeIconKey(draft.train_type_id)"
        :train-types="trainTypes"
        :disabled="!canOperate"
      />
    </AdminFieldStack>
    <p class="mrt-admin-trip-fields__field">
      <label>
        <input
          type="checkbox"
          :checked="draftIsCancelled()"
          :disabled="!canOperate"
          @change="emit('update-cancelled', ($event.target as HTMLInputElement).checked)"
        />
        {{ adminStr(cfg, 'editorDeviationCancelled') }}
      </label>
    </p>
    <AdminFieldStack
      :label="adminStr(cfg, 'editorColMessage')"
      label-for="mrt-deviation-notice"
    >
      <input
        id="mrt-deviation-notice"
        v-model="draft.notice"
        type="text"
        class="regular-text"
        :disabled="!canOperate"
      />
    </AdminFieldStack>
    <AdminFormActions v-if="canOperate">
      <MrtButton
        context="admin"
        variant="primary"
        :disabled="viewMode === 'create' && !canApplyCreate"
        @click="emit('apply')"
      >
        {{ viewMode === 'create' ? adminStr(cfg, 'editorAddDeviation') : adminStr(cfg, 'save') }}
      </MrtButton>
      <MrtButton context="admin" variant="secondary" @click="emit('back')">
        {{ adminStr(cfg, 'cancel') }}
      </MrtButton>
    </AdminFormActions>
  </div>
</template>

<style scoped>
.mrt-admin-deviation-detail__title {
  margin: 0 0 12px;
  font-size: 14px;
  font-weight: 600;
}

.mrt-admin-trip-fields {
  display: grid;
  gap: 12px;
  max-width: 28rem;
}

.mrt-admin-trip-fields__field {
  margin: 0;
}

.mrt-admin-trip-fields__field label {
  display: block;
  font-weight: 600;
  margin-bottom: 4px;
}

.mrt-admin-trip-fields__field :deep(.widefat),
.mrt-admin-trip-fields__field :deep(.regular-text),
.mrt-admin-trip-fields__field :deep(.large-text) {
  max-width: 100%;
}

.mrt-admin-trip-fields__field :deep(.description) {
  display: block;
  margin-top: 4px;
}

@media (max-width: 782px) {
  .mrt-admin-trip-fields {
    max-width: none;
  }
}
</style>
