<script setup lang="ts">
import {
  AdminBackNav,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTrainTypeCell,
  MrtButton,
} from '../ui';
import TimetableEditorTripEditForm, {
  type TripEditDraft,
} from './TimetableEditorTripEditForm.vue';
import TimetableTripFieldsBlock from './TimetableTripFieldsBlock.vue';
import type { TimetableTripDraft } from './tripFormTypes';
import type { TimetableDetail } from '../../types';
import { formatTripLineDisplay } from '../../utils/timetable-editor/tripLineDisplay';
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
    <template v-if="viewMode === 'list'">
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
