<script setup lang="ts">
import { computed, toRef } from 'vue';
import {
  AdminBackNav,
  AdminFieldStack,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTrainTypeSelect,
  AdminUnsavedBanner,
  MrtButton,
} from '../ui';
import { useDeviationsPanel } from '../../composables/timetable-editor/useDeviationsPanel';
import { useDeviationCreateDefaults } from '../../composables/timetable-editor/useDeviationCreateDefaults';
import type { TimetableDetail } from '../../types';
import {
  formatDeviationTripLabel,
  type DeviationRow,
} from '../../utils/timetable-editor/deviationsPayload';
import {
  deviationNoticePreview,
  deviationRowIsCancelled,
  deviationTrainTypeName,
  deviationTripLabelForId,
} from '../../utils/timetable-editor/deviationRowDisplay';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

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

function tripLabel(serviceId: number): string {
  return deviationTripLabelForId(props.services, serviceId);
}

function trainTypeName(typeId: number): string {
  return deviationTrainTypeName(props.trainTypes, typeId);
}

function rowIsCancelled(row: DeviationRow): boolean {
  return deviationRowIsCancelled(row, cancelledNotice.value);
}

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

    <template v-if="viewMode === 'list'">
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
            <td>{{ tripLabel(row.service_id) }}</td>
            <td>{{ trainTypeName(row.train_type_id) }}</td>
            <td>{{ rowIsCancelled(row) ? adminStr(cfg, 'yes') : '—' }}</td>
            <td>{{ deviationNoticePreview(row.notice) }}</td>
            <td v-if="canOperate">
              <AdminRowActions>
                <MrtButton context="admin" variant="secondary" @click="startEdit(idx)">
                  {{ adminStr(cfg, 'edit') }}
                </MrtButton>
                <MrtButton context="admin" variant="link-delete" @click="removeRow(idx)">
                  {{ adminStr(cfg, 'delete') }}
                </MrtButton>
              </AdminRowActions>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else class="description">{{ adminStr(cfg, 'editorDeviationsEmpty') }}</p>
      <AdminFormActions v-if="canOperate">
        <MrtButton context="admin" variant="primary" @click="onStartCreate">
          {{ adminStr(cfg, 'editorAddDeviation') }}
        </MrtButton>
      </AdminFormActions>
    </template>

    <template v-else-if="draft">
      <AdminBackNav @back="onBack" />
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
            @change="updateDraftDate(($event.target as HTMLSelectElement).value)"
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
            @change="updateDraftTrip(Number(($event.target as HTMLSelectElement).value))"
          >
            <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
            <option v-for="s in services" :key="s.id" :value="s.id">
              {{ formatDeviationTripLabel(s) }}
            </option>
          </select>
        </AdminFieldStack>
        <p v-else class="mrt-admin-trip-fields__field">
          <strong>{{ adminStr(cfg, 'editorColTrip') }}:</strong> {{ tripLabel(draft.service_id) }}
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
              @change="setDraftCancelled(($event.target as HTMLInputElement).checked)"
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
            @click="onApplyDraft"
          >
            {{ viewMode === 'create' ? adminStr(cfg, 'editorAddDeviation') : adminStr(cfg, 'save') }}
          </MrtButton>
          <MrtButton context="admin" variant="secondary" @click="onBack">
            {{ adminStr(cfg, 'cancel') }}
          </MrtButton>
        </AdminFormActions>
      </div>
    </template>

    <AdminFormActions v-if="canOperate && viewMode === 'list'">
      <MrtButton context="admin" variant="primary" @click="emit('save')">
        {{ adminStr(cfg, 'editorSaveDeviations') }}
      </MrtButton>
    </AdminFormActions>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-deviation-detail__title {
  margin: 0 0 12px;
  font-size: 14px;
  font-weight: 600;
}
</style>
