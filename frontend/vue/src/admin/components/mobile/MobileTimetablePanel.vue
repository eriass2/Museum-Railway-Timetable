<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { getDeviations, listTrainTypes, saveDeviations } from '../../api/adminRest';
import type { TimetableDetail, TrainTypeRow } from '../../types';
import { adminConfig } from '../../types';
import { adminErrorMessage, adminStr } from '../../utils/adminLabels';
import {
  createDeviationRow,
  deviationsToSavePayload,
  formatDeviationTripLabel,
  hasDeviationRow,
  type DeviationRow,
} from '../../utils/timetable-editor/deviationsPayload';
import { useAdminResource } from '../../composables/useAdminResource';
import { buildMobileTrafficTodayPayload } from '../../utils/mobile/mobileTrafficToday';
import AdminLoadState from '../AdminLoadState.vue';
import MobileQuickDeparture from './MobileQuickDeparture.vue';
import MobileCancelTraffic from './MobileCancelTraffic.vue';
import { AdminDeviationRowFields, AdminStatusMessage, MrtButton } from '../ui';

const props = defineProps<{
  timetableId: number;
  detail: TimetableDetail;
  canOperate: boolean;
  trafficToday: string | null;
}>();

const emit = defineEmits<{ saved: [message: string] }>();

const cfg = adminConfig();
const deviationRows = ref<DeviationRow[]>([]);
const trainTypes = ref<TrainTypeRow[]>([]);
const cancelMsg = ref('');
const newDate = ref('');
const newServiceId = ref(0);

const { loading, error, load, reload, data } = useAdminResource({
  fetch: async () => {
    const [deviations, types] = await Promise.all([
      getDeviations(props.timetableId),
      listTrainTypes(),
    ]);
    return { rows: deviations.rows, trainTypes: types.items };
  },
  errorMessage: (e) => adminErrorMessage(cfg, e, 'genericError'),
});

watch(
  data,
  (payload) => {
    if (!payload) {
      return;
    }
    deviationRows.value = payload.rows.map((row) => ({ ...row }));
    trainTypes.value = payload.trainTypes;
  },
  { immediate: true },
);

const cancelledNotice = computed(() => adminStr(cfg, 'trafficCancelledNotice').toLowerCase());

const canAddDeviation = computed(
  () =>
    props.canOperate &&
    props.detail.dates.length > 0 &&
    props.detail.services.length > 0 &&
    newDate.value !== '' &&
    newServiceId.value > 0 &&
    !hasDeviationRow(deviationRows.value, newServiceId.value, newDate.value),
);

watch(
  () => [props.detail.dates, props.detail.services] as const,
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

function addDeviation(): void {
  if (!canAddDeviation.value) {
    return;
  }
  const service = props.detail.services.find((s) => s.id === newServiceId.value);
  if (!service || !newDate.value) {
    return;
  }
  deviationRows.value = [...deviationRows.value, createDeviationRow(service, newDate.value)];
}

function removeDeviation(index: number): void {
  deviationRows.value = deviationRows.value.filter((_, i) => i !== index);
}

function deviationMeta(row: DeviationRow): string {
  const service = props.detail.services.find((s) => s.id === row.service_id);
  const label = service ? formatDeviationTripLabel(service) : row.trip_label;
  return `${row.date} · ${label}`;
}

const showCancelToday = computed(
  () =>
    props.trafficToday !== null &&
    props.detail.dates.includes(props.trafficToday),
);

const trafficTodayPayload = computed(() => {
  if (!props.trafficToday) return null;
  return buildMobileTrafficTodayPayload(
    props.trafficToday,
    props.timetableId,
    props.detail,
    deviationRows.value,
    cancelledNotice.value,
  );
});

function onCancelDone(message: string) {
  cancelMsg.value = message;
  emit('saved', message);
  void reload();
}

function onCancelError(message: string) {
  error.value = message;
}

async function saveDeviationChanges() {
  await saveDeviations(props.timetableId, deviationsToSavePayload(deviationRows.value));
  emit('saved', adminStr(cfg, 'editorSavedDeviations'));
}
</script>

<template>
  <div class="mrt-admin-mobile-panel">
    <p class="description mrt-admin-mobile-desktop-hint">
      {{ adminStr(cfg, 'mobileDesktopEditHint') }}
    </p>
    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'mobileLoading')"
      @retry="load"
    >
      <MobileQuickDeparture
        :services="detail.services"
        :can-edit="canOperate"
        @saved="emit('saved', $event)"
      />

      <MobileCancelTraffic
        v-if="showCancelToday && trafficTodayPayload"
        :traffic="trafficTodayPayload"
        :can-operate="canOperate"
        @done="onCancelDone"
        @error="onCancelError"
      />
      <AdminStatusMessage v-if="cancelMsg" :message="cancelMsg" />

      <div class="mrt-admin-mobile-deviations">
        <h3>{{ adminStr(cfg, 'mobileDeviationsTitle') }}</h3>
        <div v-if="canOperate" class="mrt-admin-mobile-deviation-add">
          <p class="mrt-admin-trip-fields__field">
            <label for="mrt-mobile-dev-date">{{ adminStr(cfg, 'mobileDeviationDateLabel') }}</label>
            <select id="mrt-mobile-dev-date" v-model="newDate" class="widefat">
              <option value="">{{ adminStr(cfg, 'editorDeviationDatePrompt') }}</option>
              <option v-for="d in detail.dates" :key="d" :value="d">{{ d }}</option>
            </select>
          </p>
          <p class="mrt-admin-trip-fields__field">
            <label for="mrt-mobile-dev-trip">{{ adminStr(cfg, 'mobileDeviationTripLabel') }}</label>
            <select id="mrt-mobile-dev-trip" v-model.number="newServiceId" class="widefat">
              <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
              <option v-for="s in detail.services" :key="s.id" :value="s.id">
                {{ formatDeviationTripLabel(s) }}
              </option>
            </select>
          </p>
          <MrtButton context="admin" variant="secondary" wide :disabled="!canAddDeviation" @click="addDeviation">
            {{ adminStr(cfg, 'editorAddDeviation') }}
          </MrtButton>
        </div>
        <div
          v-for="(row, idx) in deviationRows"
          :key="`${row.service_id}-${row.date}-${idx}`"
          class="mrt-admin-mobile-deviation-card"
        >
          <AdminDeviationRowFields
            :train-type-id="row.train_type_id"
            :notice="row.notice"
            :meta="deviationMeta(row)"
            :train-types="trainTypes"
            :can-operate="canOperate"
            @update:train-type-id="row.train_type_id = $event"
            @update:notice="row.notice = $event"
          />
          <p v-if="canOperate">
            <MrtButton context="admin" variant="link-delete" @click="removeDeviation(idx)">
              {{ adminStr(cfg, 'delete') }}
            </MrtButton>
          </p>
        </div>
        <p v-if="canOperate && deviationRows.length">
          <MrtButton context="admin" variant="primary" wide @click="saveDeviationChanges">
            {{ adminStr(cfg, 'editorSaveDeviations') }}
          </MrtButton>
        </p>
        <p v-else-if="!deviationRows.length" class="description">
          {{ adminStr(cfg, 'editorDeviationsEmpty') }}
        </p>
      </div>
    </AdminLoadState>
  </div>
</template>
