<script setup lang="ts">
import { computed, toRef } from 'vue';
import { AdminFormActions, AdminPanel, AdminUnsavedBanner, MrtButton } from '../ui';
import { useDeviationsPanel } from '../../composables/timetable-editor/useDeviationsPanel';
import { useDeviationCreateDefaults } from '../../composables/timetable-editor/useDeviationCreateDefaults';
import type { TimetableDetail } from '../../types';
import { type DeviationRow } from '../../utils/timetable-editor/deviationsPayload';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import TimetableEditorDeviationDraftForm from './TimetableEditorDeviationDraftForm.vue';
import TimetableEditorDeviationsList from './TimetableEditorDeviationsList.vue';

const props = defineProps<{
  canOperate: boolean;
  deviationsDirty: boolean;
  services: TimetableDetail['services'];
  dates: string[];
  trainTypes: TimetableDetail['train_types'];
  trainTypeIconKey: (typeId: number) => string;
}>();

const rows = defineModel<DeviationRow[]>('rows', { required: true });

const cfg = adminConfig();
const emit = defineEmits<{ save: [] }>();

const cancelledNotice = computed(() => adminStr(cfg, 'trafficCancelledNotice'));
const servicesRef = computed(() => props.services);
const datesRef = computed(() => props.dates);

const {
  viewMode,
  draft,
  startCreate,
  startEdit,
  requestBackToList,
  applyDraftToRows,
  removeRow,
  draftIsCancelled,
  setDraftCancelled,
  updateDraftTrip,
  updateDraftDate,
  canApplyCreate,
} = useDeviationsPanel(rows, servicesRef, datesRef, cancelledNotice);

const { newDate, newServiceId } = useDeviationCreateDefaults(
  toRef(props, 'dates'),
  toRef(props, 'services'),
);

defineExpose({ requestBackToList });

async function onBack(): Promise<void> {
  await requestBackToList();
}

async function onApplyDraft(): Promise<void> {
  if (!draft.value) {
    return;
  }
  if (viewMode.value === 'create' && !canApplyCreate.value) {
    return;
  }
  applyDraftToRows();
  await requestBackToList();
}

function onStartCreate(): void {
  if (!props.canOperate) {
    return;
  }
  startCreate(newDate.value, newServiceId.value);
}
</script>

<template>
  <AdminPanel>
    <AdminUnsavedBanner :show="deviationsDirty" :message="adminStr(cfg, 'editorDeviationsUnsaved')" />

    <TimetableEditorDeviationsList
      v-if="viewMode === 'list'"
      :rows="rows"
      :can-operate="canOperate"
      :services="services"
      :train-types="trainTypes"
      :cancelled-notice="cancelledNotice"
      @start-create="onStartCreate"
      @start-edit="startEdit"
      @remove="removeRow"
    />

    <TimetableEditorDeviationDraftForm
      v-else-if="draft"
      :view-mode="viewMode === 'create' ? 'create' : 'edit'"
      :draft="draft"
      :can-operate="canOperate"
      :services="services"
      :dates="dates"
      :train-types="trainTypes"
      :train-type-icon-key="trainTypeIconKey"
      :can-apply-create="canApplyCreate"
      :draft-is-cancelled="draftIsCancelled"
      @back="onBack"
      @apply="onApplyDraft"
      @update-date="updateDraftDate"
      @update-trip="updateDraftTrip"
      @update-cancelled="setDraftCancelled"
    />

    <AdminFormActions v-if="canOperate && viewMode === 'list'">
      <MrtButton context="admin" variant="primary" @click="emit('save')">
        {{ adminStr(cfg, 'editorSaveDeviations') }}
      </MrtButton>
    </AdminFormActions>
  </AdminPanel>
</template>
