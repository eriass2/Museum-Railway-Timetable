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
} from '../api/adminRest';
import type { TimetableDetail } from '../types';
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminDateList,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminRowActions,
  AdminTrainTypeCell,
  AdminInlineField,
} from '../components/ui';
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
import { adminConfig } from '../types';
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

function trainTypeIconKey(typeId: number): string {
  if (typeId <= 0) {
    return '';
  }
  return detail.value?.train_types.find((t) => t.id === typeId)?.icon_key ?? '';
}
const selectedServiceId = ref(0);
const deviationRows = ref<
  { service_id: number; date: string; trip_label: string; train_type_id: number; notice: string }[]
>([]);
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

const timetableTypes = [
  { value: '', label: '— Ingen färgrubrik —' },
  { value: 'green', label: 'Grön tidtabell' },
  { value: 'yellow', label: 'Gul tidtabell' },
  { value: 'red', label: 'Röd tidtabell' },
  { value: 'orange', label: 'Orange tidtabell' },
] as const;

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
    ['dates', 'Trafikdagar'],
    ['trips', 'Turer'],
    ['stoptimes', 'Stopptider'],
    ['deviations', 'Avvikelser'],
    ['preview', 'Förhandsvisning'],
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
    error.value = e instanceof Error ? e.message : 'Fel';
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
    void loadDetail();
  },
);

watch(tab, async (t) => {
  if ((t === 'preview' || t === 'stoptimes') && !overview.value) {
    try {
      await loadOverview();
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Overview fel';
    }
  }
  if (t === 'deviations' && deviationRows.value.length === 0) {
    try {
      await loadDeviations();
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Avvikelser fel';
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

async function saveDates() {
  if (!detail.value || !cfg.canManage) return;
  detail.value = await updateTimetable(timetableId.value, { dates: detail.value.dates });
  syncSnapshots();
  showSaveNotice('Trafikdagar sparade');
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
  showSaveNotice('Namn och typ sparade');
}

async function removeTimetable() {
  if (!detail.value || !cfg.canManage) return;
  const ok = await adminConfirm({
    title: 'Ta bort tidtabell',
    message: `«${detail.value.title}» och alla dess turer raderas permanent.`,
    confirmLabel: 'Ta bort',
    danger: true,
  });
  if (!ok) {
    return;
  }
  try {
    await deleteTimetable(timetableId.value);
    await router.push('/timetables');
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ta bort';
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
  const byService: Record<number, Record<string, { train_type?: number; notice?: string }>> = {};
  for (const row of deviationRows.value) {
    if (!byService[row.service_id]) byService[row.service_id] = {};
    byService[row.service_id][row.date] = {
      train_type: row.train_type_id || undefined,
      notice: row.notice || undefined,
    };
  }
  await saveDeviations(timetableId.value, byService);
  syncSnapshots();
  showSaveNotice('Avvikelser sparade');
}

function onMobileSaved(message: string) {
  showSaveNotice(message);
}
</script>

<template>
  <div>
    <h1 v-if="!detail">Tidtabell</h1>
    <AdminLoadState :loading="loading" :error="error" loading-text="Laddar tidtabell…" @retry="loadDetail">
    <p v-if="saveMsg" class="notice notice-success" role="status">{{ saveMsg }}</p>

    <AdminPanel v-if="detail && cfg.canManage" class="mrt-admin-timetable-meta">
      <p v-if="metaDirty" class="notice notice-warning mrt-admin-unsaved">
        Osparade ändringar i titel eller typ — spara innan du lämnar sidan.
      </p>
      <h2 class="screen-reader-text">Tidtabell</h2>
      <p>
        <label for="mrt-tt-title">Titel</label>
        <input id="mrt-tt-title" v-model="editTitle" type="text" class="regular-text" />
      </p>
      <p>
        <label for="mrt-tt-type">Typ (färg i översikt)</label>
        <select id="mrt-tt-type" v-model="editType">
          <option v-for="opt in timetableTypes" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </p>
      <AdminFormActions>
        <button type="button" class="button button-primary" @click="saveMeta">Spara namn och typ</button>
        <button type="button" class="button button-link-delete" @click="removeTimetable">
          Ta bort tidtabell
        </button>
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

    <AdminPanel v-if="detail && !isMobile && tab === 'dates'">
      <p v-if="datesDirty" class="notice notice-warning mrt-admin-unsaved">
        Osparade trafikdagar — klicka «Spara» för att spara listan.
      </p>
      <AdminInlineForm v-if="cfg.canManage">
        <input v-model="dateInput" type="date" />
        <button type="button" class="button" @click="addDate">Lägg till datum</button>
        <button type="button" class="button button-primary" @click="saveDates">Spara</button>
      </AdminInlineForm>
      <AdminDateList>
        <li v-for="d in detail.dates" :key="d">
          <span>{{ d }}</span>
          <button v-if="cfg.canManage" type="button" class="button-link" @click="removeDate(d)">Ta bort</button>
        </li>
      </AdminDateList>
    </AdminPanel>

    <AdminPanel v-if="detail && !isMobile && tab === 'trips'">
      <table class="widefat striped">
        <thead>
          <tr>
            <th>Rutt</th>
            <th>Tågtyp</th>
            <th>Destination</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in detail.services" :key="s.id">
            <td>{{ s.route_name }}</td>
            <td>
              <AdminTrainTypeCell
                :icon-key="s.train_type_icon_key"
                :name="s.train_type_name"
              />
            </td>
            <td>{{ s.destination || '—' }}</td>
            <td>
              <AdminRowActions>
                <button type="button" class="button" @click="selectedServiceId = s.id; tab = 'stoptimes'">
                  Stopptider
                </button>
                <button
                  v-if="cfg.canManage"
                  type="button"
                  class="button button-link-delete"
                  @click="removeTrip(s.id)"
                >
                  Ta bort
                </button>
              </AdminRowActions>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="cfg.canManage" class="mrt-admin-trip-form">
        <select v-model.number="newTrip.route_id">
          <option :value="0">— Rutt —</option>
          <option v-for="r in detail.routes" :key="r.id" :value="r.id">{{ r.title }}</option>
        </select>
        <AdminInlineField>
          <AdminTrainTypeCell
            v-if="trainTypeIconKey(newTrip.train_type_id)"
            :icon-key="trainTypeIconKey(newTrip.train_type_id)"
          />
          <select v-model.number="newTrip.train_type_id">
            <option :value="0">— Tågtyp —</option>
            <option v-for="t in detail.train_types" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </AdminInlineField>
        <select v-model.number="newTrip.end_station_id">
          <option :value="0">— Destination —</option>
          <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>
        <div class="mrt-admin-trip-form__actions">
          <button type="button" class="button button-primary" @click="addTrip">Lägg till tur</button>
        </div>
      </div>
    </AdminPanel>

    <AdminPanel v-if="detail && !isMobile && tab === 'stoptimes'" class="mrt-vue-root">
      <p class="description">Klicka i rutorna för att ändra tid, stannar och P/A. Sparas automatiskt per tur.</p>
      <EditableTimetableOverview
        v-if="overview"
        :data="overview"
        :readonly="!cfg.canManage && !cfg.canOperate"
      />
      <details class="mrt-mt-sm">
        <summary>Tabellvy för en tur</summary>
        <p>
          <label>Tur:</label>
          <select v-model.number="selectedServiceId">
            <option :value="0">— Välj tur —</option>
            <option v-for="s in detail.services" :key="s.id" :value="s.id">{{ s.title }}</option>
          </select>
        </p>
        <StopTimesEditor v-if="selectedServiceId" :service-id="selectedServiceId" />
      </details>
    </AdminPanel>

    <AdminPanel v-if="detail && !isMobile && tab === 'deviations'">
      <p v-if="deviationsDirty" class="notice notice-warning mrt-admin-unsaved">
        Osparade avvikelser — klicka «Spara avvikelser».
      </p>
      <table class="widefat striped">
        <thead>
          <tr>
            <th>Datum</th>
            <th>Tur</th>
            <th>Tågtyp</th>
            <th>Meddelande</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idx) in deviationRows" :key="idx">
            <td>{{ row.date }}</td>
            <td>{{ row.trip_label }}</td>
            <td>
              <AdminInlineField>
                <AdminTrainTypeCell
                  v-if="trainTypeIconKey(row.train_type_id)"
                  :icon-key="trainTypeIconKey(row.train_type_id)"
                />
                <select v-model.number="row.train_type_id" :disabled="!cfg.canOperate">
                  <option :value="0">— Standard —</option>
                  <option v-for="t in detail.train_types" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
              </AdminInlineField>
            </td>
            <td>
              <input v-model="row.notice" type="text" class="regular-text" :disabled="!cfg.canOperate" />
            </td>
          </tr>
        </tbody>
      </table>
      <AdminFormActions v-if="cfg.canOperate">
        <button type="button" class="button button-primary" @click="saveDeviationChanges">Spara avvikelser</button>
      </AdminFormActions>
    </AdminPanel>

    <AdminPanel v-if="!isMobile && tab === 'preview'" class="mrt-vue-root">
      <MrtTimetableOverviewView v-if="overview" :data="overview" :labels="overviewLabels" />
    </AdminPanel>
    </AdminLoadState>
  </div>
</template>
