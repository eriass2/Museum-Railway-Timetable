<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import {
  createRoute,
  createStation,
  deleteRoute,
  deleteStation,
  listRoutes,
  listStations,
  updateRoute,
  updateStation,
} from '../api/adminRest';
import type { RouteRow, StationRow } from '../types';
import AdminNav from '../components/AdminNav.vue';
import RoutePreview from '../components/RoutePreview.vue';
import { adminConfirm } from '../composables/adminConfirm';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const stations = ref<StationRow[]>([]);
const routes = ref<RouteRow[]>([]);
const loading = ref(true);
const error = ref('');
const newStationTitle = ref('');
const newRouteTitle = ref('');
const sectionTab = ref<'stations' | 'routes'>('stations');
const editingRoute = ref<RouteRow | null>(null);
const addStationToRoute = ref(0);

const stationsById = computed(
  () =>
    new Map(
      stations.value.map((st) => [
        st.id,
        { title: st.title, station_type: st.station_type },
      ]),
    ),
);

async function load() {
  loading.value = true;
  try {
    const [s, r] = await Promise.all([listStations(), listRoutes()]);
    stations.value = s.items;
    routes.value = r.items;
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Fel';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void load();
});

async function addStation() {
  if (!cfg.canManage || !newStationTitle.value.trim()) return;
  await createStation({ title: newStationTitle.value.trim() });
  newStationTitle.value = '';
  await load();
}

async function addRoute() {
  if (!cfg.canManage || !newRouteTitle.value.trim()) return;
  await createRoute({ title: newRouteTitle.value.trim(), station_ids: [] });
  newRouteTitle.value = '';
  await load();
}

function editRoute(route: RouteRow) {
  sectionTab.value = 'routes';
  editingRoute.value = { ...route, station_ids: [...route.station_ids] };
}

async function saveRoute() {
  if (!editingRoute.value || !cfg.canManage) return;
  await updateRoute(editingRoute.value.id, {
    title: editingRoute.value.title,
    start_station: editingRoute.value.start_station,
    end_station: editingRoute.value.end_station,
    station_ids: editingRoute.value.station_ids,
  });
  editingRoute.value = null;
  await load();
}

function moveStation(idx: number, dir: -1 | 1) {
  if (!editingRoute.value) return;
  const next = idx + dir;
  const ids = editingRoute.value.station_ids;
  if (next < 0 || next >= ids.length) return;
  const copy = [...ids];
  const tmp = copy[idx];
  copy[idx] = copy[next];
  copy[next] = tmp;
  editingRoute.value.station_ids = copy;
}

function appendStationToRoute() {
  if (!editingRoute.value || !addStationToRoute.value) return;
  if (!editingRoute.value.station_ids.includes(addStationToRoute.value)) {
    editingRoute.value.station_ids = [...editingRoute.value.station_ids, addStationToRoute.value];
  }
  addStationToRoute.value = 0;
}

async function saveStationMeta(st: StationRow) {
  if (!cfg.canManage) return;
  await updateStation(st.id, st);
}

async function removeStation(st: StationRow) {
  if (!cfg.canManage) return;
  const ok = await adminConfirm({
    title: 'Ta bort station',
    message: `Stationen «${st.title}» tas bort om den inte används i rutter eller turer.`,
    confirmLabel: 'Ta bort',
    danger: true,
  });
  if (!ok) return;
  error.value = '';
  try {
    await deleteStation(st.id);
    await load();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ta bort station';
  }
}

async function removeRoute(route: RouteRow) {
  if (!cfg.canManage) return;
  const ok = await adminConfirm({
    title: 'Ta bort rutt',
    message: `Rutten «${route.title}» tas bort om inga turer använder den.`,
    confirmLabel: 'Ta bort',
    danger: true,
  });
  if (!ok) return;
  error.value = '';
  try {
    await deleteRoute(route.id);
    if (editingRoute.value?.id === route.id) {
      editingRoute.value = null;
    }
    await load();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ta bort rutt';
  }
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>Stationer &amp; rutter</h1>
    <AdminNav />
    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>

    <nav v-if="!loading" class="nav-tab-wrapper mrt-admin-section-nav" aria-label="Stationer eller rutter">
      <a
        href="#"
        class="nav-tab"
        :class="{ 'nav-tab-active': sectionTab === 'stations' }"
        @click.prevent="sectionTab = 'stations'"
      >
        Stationer
      </a>
      <a
        href="#"
        class="nav-tab"
        :class="{ 'nav-tab-active': sectionTab === 'routes' }"
        @click.prevent="sectionTab = 'routes'"
      >
        Rutter
      </a>
    </nav>

    <div v-if="!loading && sectionTab === 'stations'" class="mrt-admin-panel">
      <h2 class="screen-reader-text">Stationer</h2>
      <p v-if="cfg.canManage" class="mrt-admin-create-form">
        <input v-model="newStationTitle" type="text" class="regular-text" placeholder="Ny station" />
        <button type="button" class="button button-primary" @click="addStation">Lägg till</button>
      </p>
      <div class="mrt-admin-table-scroll">
      <table class="widefat striped">
        <thead>
          <tr>
            <th>Namn</th>
            <th>Typ</th>
            <th>Lat</th>
            <th>Lng</th>
            <th>Buss</th>
            <th>Ordning</th>
            <th v-if="cfg.canManage"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="st in stations" :key="st.id">
            <td>
              <input v-if="cfg.canManage" v-model="st.title" type="text" class="regular-text" />
              <span v-else>{{ st.title }}</span>
            </td>
            <td>
              <select v-if="cfg.canManage" v-model="st.station_type">
                <option value="">—</option>
                <option value="station">Station</option>
                <option value="halt">Hållplats</option>
                <option value="depot">Depot</option>
                <option value="museum">Museum</option>
              </select>
              <span v-else>{{ st.station_type || '—' }}</span>
            </td>
            <td>
              <input
                v-if="cfg.canManage"
                v-model="st.lat"
                type="text"
                class="small-text"
                placeholder="57.48"
              />
              <span v-else>{{ st.lat || '—' }}</span>
            </td>
            <td>
              <input
                v-if="cfg.canManage"
                v-model="st.lng"
                type="text"
                class="small-text"
                placeholder="15.82"
              />
              <span v-else>{{ st.lng || '—' }}</span>
            </td>
            <td>
              <input
                v-if="cfg.canManage"
                v-model="st.bus_suffix"
                type="checkbox"
              />
              <span v-else>{{ st.bus_suffix ? 'Ja' : '—' }}</span>
            </td>
            <td>
              <input v-if="cfg.canManage" v-model.number="st.display_order" type="number" class="small-text" />
              <span v-else>{{ st.display_order }}</span>
            </td>
            <td v-if="cfg.canManage">
              <button type="button" class="button button-small" @click="saveStationMeta(st)">Spara</button>
              <button type="button" class="button button-link-delete" @click="removeStation(st)">
                Ta bort
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <div v-if="!loading && sectionTab === 'routes'" class="mrt-admin-panel">
      <h2 class="screen-reader-text">Rutter</h2>
      <p v-if="cfg.canManage" class="mrt-admin-create-form">
        <input v-model="newRouteTitle" type="text" class="regular-text" placeholder="Ny rutt" />
        <button type="button" class="button button-primary" @click="addRoute">Lägg till</button>
      </p>
      <div class="mrt-admin-table-scroll">
      <table class="widefat striped">
        <thead>
          <tr>
            <th>Namn</th>
            <th>Stationer</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="route in routes" :key="route.id">
            <td>{{ route.title }}</td>
            <td>
              <RoutePreview
                :station-ids="route.station_ids"
                :stations-by-id="stationsById"
                :start-station-id="route.start_station"
                :end-station-id="route.end_station"
                compact
              />
            </td>
            <td>
              <button v-if="cfg.canManage" type="button" class="button button-small" @click="editRoute(route)">
                Redigera
              </button>
              <button
                v-if="cfg.canManage"
                type="button"
                class="button button-link-delete"
                @click="removeRoute(route)"
              >
                Ta bort
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <div
      v-if="!loading && sectionTab === 'routes' && editingRoute"
      class="mrt-admin-panel mrt-admin-route-editor"
    >
      <h2>Redigera rutt: {{ editingRoute.title }}</h2>
      <p>
        <input v-model="editingRoute.title" type="text" class="regular-text" />
      </p>
      <p>
        <label>Start:</label>
        <select v-model.number="editingRoute.start_station">
          <option :value="0">—</option>
          <option v-for="st in stations" :key="st.id" :value="st.id">{{ st.title }}</option>
        </select>
        <label>Slut:</label>
        <select v-model.number="editingRoute.end_station">
          <option :value="0">—</option>
          <option v-for="st in stations" :key="st.id" :value="st.id">{{ st.title }}</option>
        </select>
      </p>
      <RoutePreview
        v-if="editingRoute"
        :station-ids="editingRoute.station_ids"
        :stations-by-id="stationsById"
        :start-station-id="editingRoute.start_station"
        :end-station-id="editingRoute.end_station"
        label="Förhandsgranskning av rutt"
      />
      <ol>
        <li v-for="(sid, idx) in editingRoute.station_ids" :key="sid">
          {{ stations.find((s) => s.id === sid)?.title || sid }}
          <button type="button" class="button button-small" @click="moveStation(idx, -1)">↑</button>
          <button type="button" class="button button-small" @click="moveStation(idx, 1)">↓</button>
        </li>
      </ol>
      <p>
        <select v-model.number="addStationToRoute">
          <option :value="0">Lägg till station...</option>
          <option v-for="st in stations" :key="st.id" :value="st.id">{{ st.title }}</option>
        </select>
        <button type="button" class="button" @click="appendStationToRoute">Lägg till</button>
      </p>
      <p>
        <button type="button" class="button button-primary" @click="saveRoute">Spara rutt</button>
        <button type="button" class="button" @click="editingRoute = null">Avbryt</button>
      </p>
    </div>
  </div>
</template>
