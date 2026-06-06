<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import {
  addTimetableService,
  deleteTimetable,
  getDeviations,
  getRouteDestinations,
  getTimetable,
  getTimetableOverview,
  removeTimetableService,
  saveDeviations,
  updateTimetable,
  updateTimetableService,
} from '../api/adminRest';
import type { TimetableDetail, TimetableServiceRow } from '../types';
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminFormActions,
  AdminPanel,
  AdminStatusMessage,
  AdminUnsavedBanner,
  MrtButton,
} from '../components/ui';
import TimetableEditorDatesTab from '../components/timetable-editor/TimetableEditorDatesTab.vue';
import TimetableEditorDeviationsTab from '../components/timetable-editor/TimetableEditorDeviationsTab.vue';
import TimetableEditorTripEditForm, {
  type TripEditDraft,
} from '../components/timetable-editor/TimetableEditorTripEditForm.vue';
import TimetableEditorTripsTab from '../components/timetable-editor/TimetableEditorTripsTab.vue';
import StopTimesEditor from '../components/StopTimesEditor.vue';
import EditableTimetableOverview from '../components/EditableTimetableOverview.vue';
import MobileTimetablePanel from '../components/MobileTimetablePanel.vue';
import MrtTimetableOverviewView from '../../components/overview/MrtTimetableOverviewView.vue';
import { overviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';
import { adminConfirm } from '../composables/adminConfirm';
import { useAdminSaveNotice } from '../composables/useAdminSaveNotice';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { useTimetableEditorDirty } from '../composables/useTimetableEditorDirty';
import { deviationsToSavePayload, type DeviationRow } from '../utils/deviationsPayload';
import { adminConfig } from '../types';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
import { useRouter } from 'vue-router';

const router = useRouter();
const overviewLabels = overviewUiLabels({});

const props = defineProps<{ id: string }>();
const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const timetableId = computed(() => Number(props.id));

const tab = ref<'dates' | 'trips' | 'stoptimes' | 'deviations' | 'preview'>('dates');
const detail = ref<TimetableDetail | null>(null);
const overview = ref<TimetableOverviewPayload | null>(null);
const loading = ref(true);
const error = ref('');
const dateInput = ref('');
const newTrip = ref({ route_id: 0, train_type_id: 0, end_station_id: 0 });
const destinations = ref<{ id: number; name: string }[]>([]);
const editTrip = ref<TripEditDraft | null>(null);
const editDestinations = ref<{ id: number; name: string }[]>([]);

function trainTypeIconKey(typeId: number): string {
  if (typeId <= 0) {
    return '';
  }
  return detail.value?.train_types.find((t) => t.id === typeId)?.icon_key ?? '';
}
const selectedServiceId = ref(0);
const gridOverviewLoading = ref(false);
const deviationRows = ref<DeviationRow[]>([]);
const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
const editTitle = ref('');
const editType = ref('');

const {
  syncSnapshots,
  metaDirty,
  datesDirty,
  deviationsDirty,
  tabLabel,
} = useTimetableEditorDirty(detail, editTitle, editType, deviationRows);

const timetableTypes = computed(() => [
  { value: '', label: adminStr(cfg, 'editorTypeNone') },
  { value: 'green', label: adminStr(cfg, 'editorTypeGreen') },
  { value: 'yellow', label: adminStr(cfg, 'editorTypeYellow') },
  { value: 'red', label: adminStr(cfg, 'editorTypeRed') },
  { value: 'orange', label: adminStr(cfg, 'editorTypeOrange') },
] as const);

const trafficToday = computed(() => {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
});

const desktopTabs = computed(() => {
  if (isMobile.value) return [];
  return [
    ['dates', adminStr(cfg, 'editorTabDates')],
    ['trips', adminStr(cfg, 'editorTabTrips')],
    ['stoptimes', adminStr(cfg, 'editorTabStoptimes')],
    ['deviations', adminStr(cfg, 'editorTabDeviations')],
    ['preview', adminStr(cfg, 'editorTabPreview')],
  ] as const;
});

async function loadDetail() {
  loading.value = true;
  error.value = '';
  try {
    detail.value = await getTimetable(timetableId.value);
    editTitle.value = detail.value.title;
    editType.value = detail.value.type || '';
    syncSnapshots();
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'genericError');
  } finally {
    loading.value = false;
  }
}

async function loadOverview() {
  overview.value = await getTimetableOverview(timetableId.value);
}

async function loadDeviations() {
  const res = await getDeviations(timetableId.value);
  deviationRows.value = res.rows;
  syncSnapshots();
}

onMounted(() => {
  void loadDetail();
});

watch(
  () => props.id,
  () => {
    selectedServiceId.value = 0;
    overview.value = null;
    void loadDetail();
  },
);

function ensureDefaultTripSelection(): void {
  if (selectedServiceId.value > 0 || !detail.value?.services.length) {
    return;
  }
  selectedServiceId.value = detail.value.services[0].id;
}

async function onStoptimesGridToggle(event: Event): Promise<void> {
  const el = event.target as HTMLDetailsElement;
  if (!el.open || overview.value) {
    return;
  }
  gridOverviewLoading.value = true;
  try {
    await loadOverview();
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'editorOverviewLoadFailed');
  } finally {
    gridOverviewLoading.value = false;
  }
}

watch(tab, async (t) => {
  if (t === 'stoptimes') {
    ensureDefaultTripSelection();
  }
  if (t === 'preview' && !overview.value) {
    try {
      await loadOverview();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'editorOverviewLoadFailed');
    }
  }
  if (t === 'deviations' && deviationRows.value.length === 0) {
    try {
      await loadDeviations();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'editorDeviationsLoadFailed');
    }
  }
});

watch(
  () => newTrip.value.route_id,
  async (routeId) => {
    newTrip.value.end_station_id = 0;
    destinations.value = routeId ? (await getRouteDestinations(routeId)).destinations : [];
  },
);

function serviceNumberForEdit(service: TimetableServiceRow): string {
  return service.service_number === String(service.id) ? '' : service.service_number;
}

async function loadEditDestinations(routeId: number, resetEnd = false): Promise<void> {
  if (editTrip.value && resetEnd) {
    editTrip.value.end_station_id = 0;
  }
  editDestinations.value = routeId ? (await getRouteDestinations(routeId)).destinations : [];
}

async function startEditTrip(serviceId: number): Promise<void> {
  const service = detail.value?.services.find((s) => s.id === serviceId);
  if (!service || !cfg.canManage) {
    return;
  }
  editTrip.value = {
    service_id: service.id,
    service_number: serviceNumberForEdit(service),
    route_id: service.route_id,
    train_type_id: service.train_type_id,
    end_station_id: service.end_station_id ?? 0,
  };
  await loadEditDestinations(service.route_id);
}

function cancelEditTrip(): void {
  editTrip.value = null;
  editDestinations.value = [];
}

async function saveEditTrip(): Promise<void> {
  if (!editTrip.value || !cfg.canManage || editTrip.value.route_id <= 0) {
    return;
  }
  await updateTimetableService(timetableId.value, editTrip.value.service_id, {
    route_id: editTrip.value.route_id,
    train_type_id: editTrip.value.train_type_id || undefined,
    end_station_id: editTrip.value.end_station_id || undefined,
    service_number: editTrip.value.service_number,
  });
  editTrip.value = null;
  editDestinations.value = [];
  await loadDetail();
  showSaveNotice(adminStr(cfg, 'editorSavedTrip'));
}

async function saveDates() {
  if (!detail.value || !cfg.canManage) return;
  detail.value = await updateTimetable(timetableId.value, { dates: detail.value.dates });
  syncSnapshots();
  showSaveNotice(adminStr(cfg, 'editorSavedDates'));
}

async function saveMeta() {
  if (!detail.value || !cfg.canManage) return;
  detail.value = await updateTimetable(timetableId.value, {
    title: editTitle.value.trim(),
    type: editType.value,
  });
  editTitle.value = detail.value.title;
  editType.value = detail.value.type || '';
  syncSnapshots();
  showSaveNotice(adminStr(cfg, 'editorSavedMeta'));
}

async function removeTimetable() {
  if (!detail.value || !cfg.canManage) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'timetablesDeleteTitle'),
    message: adminFmt(cfg, 'timetablesDeleteMessage', detail.value.title),
    confirmLabel: adminStr(cfg, 'delete'),
    danger: true,
  });
  if (!ok) {
    return;
  }
  try {
    await deleteTimetable(timetableId.value);
    await router.push('/timetables');
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'timetablesDeleteFailed');
  }
}

function addDate() {
  if (!detail.value || !dateInput.value) return;
  if (!detail.value.dates.includes(dateInput.value)) {
    detail.value.dates = [...detail.value.dates, dateInput.value].sort();
  }
  dateInput.value = '';
}

function removeDate(d: string) {
  if (!detail.value) return;
  detail.value.dates = detail.value.dates.filter((x) => x !== d);
}

async function addTrip() {
  if (!cfg.canManage) return;
  await addTimetableService(timetableId.value, {
    route_id: newTrip.value.route_id,
    train_type_id: newTrip.value.train_type_id || undefined,
    end_station_id: newTrip.value.end_station_id || undefined,
  });
  await loadDetail();
}

async function removeTrip(serviceId: number) {
  if (!cfg.canManage) return;
  await removeTimetableService(timetableId.value, serviceId);
  await loadDetail();
}

async function saveDeviationChanges() {
  await saveDeviations(timetableId.value, deviationsToSavePayload(deviationRows.value));
  syncSnapshots();
  showSaveNotice(adminStr(cfg, 'editorSavedDeviations'));
}

function openStoptimes(serviceId: number) {
  selectedServiceId.value = serviceId;
  tab.value = 'stoptimes';
}

function onMobileSaved(message: string) {
  showSaveNotice(message);
}
</script>

<template>
  <div>
    <h1 v-if="!detail">{{ adminStr(cfg, 'editorTitle') }}</h1>
    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'editorLoading')"
      @retry="loadDetail"
    >
    <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />

    <AdminPanel v-if="detail && cfg.canManage" class="mrt-admin-timetable-meta">
      <AdminUnsavedBanner :show="metaDirty" :message="adminStr(cfg, 'editorMetaUnsaved')" />
      <h2 class="screen-reader-text">{{ adminStr(cfg, 'editorTitle') }}</h2>
      <p>
        <label for="mrt-tt-title">{{ adminStr(cfg, 'editorTitleLabel') }}</label>
        <input id="mrt-tt-title" v-model="editTitle" type="text" class="regular-text" />
      </p>
      <p>
        <label for="mrt-tt-type">{{ adminStr(cfg, 'editorTypeLabel') }}</label>
        <select id="mrt-tt-type" v-model="editType">
          <option v-for="opt in timetableTypes" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </p>
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="saveMeta">
          {{ adminStr(cfg, 'editorSaveMeta') }}
        </MrtButton>
        <MrtButton context="admin" variant="link-delete" @click="removeTimetable">
          {{ adminStr(cfg, 'editorDeleteTimetable') }}
        </MrtButton>
      </AdminFormActions>
    </AdminPanel>
    <h1 v-else-if="detail">{{ detail.title }}</h1>

    <MobileTimetablePanel
      v-if="detail && isMobile"
      :timetable-id="timetableId"
      :detail="detail"
      :can-operate="cfg.canOperate"
      :traffic-today="trafficToday"
      @saved="onMobileSaved"
    />

    <nav v-if="detail && desktopTabs.length" class="nav-tab-wrapper">
      <a
        v-for="t in desktopTabs"
        :key="t[0]"
        href="#"
        class="nav-tab"
        :class="{ 'nav-tab-active': tab === t[0] }"
        @click.prevent="tab = t[0]"
      >
        {{ tabLabel(t[1], t[0]) }}
      </a>
    </nav>

    <TimetableEditorDatesTab
      v-if="detail && !isMobile && tab === 'dates'"
      v-model:date-input="dateInput"
      :can-manage="cfg.canManage"
      :dates-dirty="datesDirty"
      :dates="detail.dates"
      @add="addDate"
      @remove="removeDate"
      @save="saveDates"
    />

    <TimetableEditorTripsTab
      v-if="detail && !isMobile && tab === 'trips'"
      v-model:new-trip="newTrip"
      :can-manage="cfg.canManage"
      :detail="detail"
      :destinations="destinations"
      :editing-trip-id="editTrip?.service_id ?? 0"
      :train-type-icon-key="trainTypeIconKey"
      @open-stoptimes="openStoptimes"
      @start-edit="startEditTrip"
      @remove-trip="removeTrip"
      @add-trip="addTrip"
    />

    <TimetableEditorTripEditForm
      v-if="detail && !isMobile && tab === 'trips' && editTrip"
      v-model:draft="editTrip"
      :detail="detail"
      :destinations="editDestinations"
      :train-type-icon-key="trainTypeIconKey"
      @route-change="loadEditDestinations(editTrip.route_id, true)"
      @save="saveEditTrip"
      @cancel="cancelEditTrip"
    />

    <AdminPanel v-if="detail && !isMobile && tab === 'stoptimes'" class="mrt-vue-root">
      <p class="description">{{ adminStr(cfg, 'editorStoptimesHint') }}</p>
      <p class="mrt-admin-stoptimes-trip-picker">
        <label for="mrt-stoptimes-service">{{ adminStr(cfg, 'editorStoptimesTripLabel') }}</label>
        <select id="mrt-stoptimes-service" v-model.number="selectedServiceId">
          <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
          <option v-for="s in detail.services" :key="s.id" :value="s.id">
            {{ s.service_number }} — {{ s.destination || s.route_name }}
          </option>
        </select>
      </p>
      <StopTimesEditor v-if="selectedServiceId" :service-id="selectedServiceId" />
      <details class="mrt-mt-sm mrt-admin-stoptimes-grid" @toggle="onStoptimesGridToggle">
        <summary>{{ adminStr(cfg, 'editorStoptimesGridSummary') }}</summary>
        <p class="description">{{ adminStr(cfg, 'editorStoptimesGridHint') }}</p>
        <p v-if="gridOverviewLoading" class="description">{{ adminStr(cfg, 'editorLoading') }}</p>
        <EditableTimetableOverview
          v-else-if="overview"
          :data="overview"
          :readonly="!cfg.canManage && !cfg.canOperate"
          @refresh-needed="loadOverview"
        />
      </details>
    </AdminPanel>

    <TimetableEditorDeviationsTab
      v-if="detail && !isMobile && tab === 'deviations'"
      v-model:rows="deviationRows"
      :can-operate="cfg.canOperate"
      :deviations-dirty="deviationsDirty"
      :services="detail.services"
      :dates="detail.dates"
      :train-types="detail.train_types"
      :train-type-icon-key="trainTypeIconKey"
      @save="saveDeviationChanges"
    />

    <AdminPanel v-if="!isMobile && tab === 'preview'" class="mrt-vue-root">
      <MrtTimetableOverviewView v-if="overview" :data="overview" :labels="overviewLabels" />
    </AdminPanel>
    </AdminLoadState>
  </div>
</template>
