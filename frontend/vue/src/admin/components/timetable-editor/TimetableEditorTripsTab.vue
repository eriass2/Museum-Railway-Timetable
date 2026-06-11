<script setup lang="ts">
import {
  AdminBackNav,
  AdminFormActions,
  AdminPanel,
  MrtButton,
} from '../ui';
import TimetableEditorTripEditForm, {
  type TripEditDraft,
} from './TimetableEditorTripEditForm.vue';
import TimetableEditorTripsList from './TimetableEditorTripsList.vue';
import TimetableTripFieldsBlock from './TimetableTripFieldsBlock.vue';
import type { TimetableTripDraft } from './tripFormTypes';
import type { TimetableDetail } from '../../types';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

export type TripsPanelView = 'list' | 'create' | 'edit';

defineProps<{
  canManage: boolean;
  detail: TimetableDetail;
  trainTypeIconKey: (typeId: number) => string;
  viewMode: TripsPanelView;
}>();

const cfg = adminConfig();
const newTrip = defineModel<TimetableTripDraft>('newTrip', { required: true });
const editDraft = defineModel<TripEditDraft | null>('editTrip', { required: true });

const emit = defineEmits<{
  back: [];
  'open-stoptimes': [serviceId: number];
  'start-create': [];
  'start-edit': [serviceId: number];
  'remove-trip': [serviceId: number];
  'add-trip': [];
  'save-edit': [];
}>();
</script>

<template>
  <AdminPanel>
    <TimetableEditorTripsList
      v-if="viewMode === 'list'"
      :can-manage="canManage"
      :detail="detail"
      @start-create="emit('start-create')"
      @start-edit="emit('start-edit', $event)"
      @open-stoptimes="emit('open-stoptimes', $event)"
      @remove-trip="emit('remove-trip', $event)"
    />

    <template v-else-if="viewMode === 'create'">
      <AdminBackNav @back="emit('back')" />
      <div class="mrt-admin-trip-create">
        <h3 class="mrt-admin-trip-create__title">{{ adminStr(cfg, 'editorAddTrip') }}</h3>
        <TimetableTripFieldsBlock
          v-model:draft="newTrip"
          :detail="detail"
          field-id-prefix="trip-new"
          :train-type-icon-key="trainTypeIconKey"
        />
        <AdminFormActions class="mrt-admin-trip-create__actions">
          <MrtButton context="admin" variant="primary" @click="emit('add-trip')">
            {{ adminStr(cfg, 'editorAddTrip') }}
          </MrtButton>
          <MrtButton context="admin" variant="secondary" @click="emit('back')">
            {{ adminStr(cfg, 'cancel') }}
          </MrtButton>
        </AdminFormActions>
      </div>
    </template>

    <template v-else-if="viewMode === 'edit' && editDraft">
      <AdminBackNav @back="emit('back')" />
      <TimetableEditorTripEditForm
        v-model:draft="editDraft"
        :detail="detail"
        :train-type-icon-key="trainTypeIconKey"
        embedded
        @save="emit('save-edit')"
        @cancel="emit('back')"
      />
    </template>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-trip-create__title {
  margin: 0 0 12px;
  font-size: 14px;
  font-weight: 600;
}

.mrt-admin-trip-create__actions {
  margin-top: 16px;
}
</style>
