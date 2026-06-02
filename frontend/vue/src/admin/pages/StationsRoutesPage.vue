<script setup lang="ts">
import { computed, ref, watch } from 'vue';
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
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminEmptyState,
  AdminFlashRow,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminRowActions,
  AdminStatusMessage,
  AdminTableScroll,
  MrtButton,
} from '../components/ui';
import RoutePreview from '../components/RoutePreview.vue';
import { routePreviewTypeLabel } from '../utils/routePreviewNodes';
import { adminConfirm } from '../composables/adminConfirm';
import { useAdminResource } from '../composables/useAdminResource';
import { useAdminRowFlash } from '../composables/useAdminRowFlash';
import { useAdminSaveNotice } from '../composables/useAdminSaveNotice';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const stations = ref<StationRow[]>([]);
const routes = ref<RouteRow[]>([]);
const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
const { flashRow, isFlashed } = useAdminRowFlash();
const newStationTitle = ref('');
const newRouteTitle = ref('');
const sectionTab = ref<'stations' | 'routes'>('stations');
const editingRoute = ref<RouteRow | null>(null);
const addStationToRoute = ref(0);

const { loading, error, data, load, reload } = useAdminResource({
  fetch: async () => {
    const [s, r] = await Promise.all([listStations(), listRoutes()]);
    return { stations: s.items, routes: r.items };
  },
  errorMessage: (e) => adminErrorMessage(cfg, e, 'loadFailed'),
});

watch(
  data,
  (payload) => {
    if (!payload) {
      return;
    }
    stations.value = payload.stations.map((row) => ({ ...row }));
    routes.value = payload.routes.map((row) => ({ ...row }));
  },
  { immediate: true },
);

const stationsById = computed(
  () =>
    new Map(
      stations.value.map((st) => [
        st.id,
        { title: st.title, station_type: st.station_type },
      ]),
    ),
);

async function addStation() {
  if (!cfg.canManage || !newStationTitle.value.trim()) return;
  await createStation({ title: newStationTitle.value.trim() });
  newStationTitle.value = '';
  await reload();
}

async function addRoute() {
  if (!cfg.canManage || !newRouteTitle.value.trim()) return;
  await createRoute({ title: newRouteTitle.value.trim(), station_ids: [] });
  newRouteTitle.value = '';
  await reload();
}

function editRoute(route: RouteRow) {
  sectionTab.value = 'routes';
  editingRoute.value = { ...route, station_ids: [...route.station_ids] };
}

async function saveRoute() {
  if (!editingRoute.value || !cfg.canManage) return;
  const title = editingRoute.value.title;
  const routeId = editingRoute.value.id;
  await updateRoute(routeId, {
    title: editingRoute.value.title,
    start_station: editingRoute.value.start_station,
    end_station: editingRoute.value.end_station,
    station_ids: editingRoute.value.station_ids,
  });
  editingRoute.value = null;
  showSaveNotice(adminFmt(cfg, 'stationsRouteSaved', title));
  flashRow(routeId);
  await reload();
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
  showSaveNotice(adminFmt(cfg, 'stationsStationSaved', st.title));
  flashRow(st.id);
}

async function removeStation(st: StationRow) {
  if (!cfg.canManage) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'stationsDeleteStationTitle'),
    message: adminFmt(cfg, 'stationsDeleteStationMsg', st.title),
    confirmLabel: adminStr(cfg, 'delete'),
    danger: true,
  });
  if (!ok) return;
  error.value = '';
  try {
    await deleteStation(st.id);
    await reload();
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'stationsDeleteStationFailed');
  }
}

async function removeRoute(route: RouteRow) {
  if (!cfg.canManage) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'stationsDeleteRouteTitle'),
    message: adminFmt(cfg, 'stationsDeleteRouteMsg', route.title),
    confirmLabel: adminStr(cfg, 'delete'),
    danger: true,
  });
  if (!ok) return;
  error.value = '';
  try {
    await deleteRoute(route.id);
    if (editingRoute.value?.id === route.id) {
      editingRoute.value = null;
    }
    await reload();
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'stationsDeleteRouteFailed');
  }
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'stationsTitle') }}</h1>
    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'stationsLoading')"
      @retry="load"
    >
    <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
    <nav class="nav-tab-wrapper mrt-admin-section-nav" :aria-label="adminStr(cfg, 'stationsNavAria')">
      <a
        href="#"
        class="nav-tab"
        :class="{ 'nav-tab-active': sectionTab === 'stations' }"
        @click.prevent="sectionTab = 'stations'"
      >
        {{ adminStr(cfg, 'stationsTabStations') }}
      </a>
      <a
        href="#"
        class="nav-tab"
        :class="{ 'nav-tab-active': sectionTab === 'routes' }"
        @click.prevent="sectionTab = 'routes'"
      >
        {{ adminStr(cfg, 'stationsTabRoutes') }}
      </a>
    </nav>

    <AdminPanel v-if="sectionTab === 'stations'">
      <h2 class="screen-reader-text">{{ adminStr(cfg, 'stationsTabStations') }}</h2>
      <AdminInlineForm v-if="cfg.canManage">
        <input
          v-model="newStationTitle"
          type="text"
          class="regular-text"
          :placeholder="adminStr(cfg, 'stationsNewStation')"
        />
        <MrtButton context="admin" variant="primary" @click="addStation">
          {{ adminStr(cfg, 'add') }}
        </MrtButton>
      </AdminInlineForm>
      <AdminEmptyState
        v-if="!stations.length"
        :title="adminStr(cfg, 'stationsEmptyStationsTitle')"
        :message="adminStr(cfg, 'stationsEmptyStationsMsg')"
      />
      <AdminTableScroll v-else>
      <table class="widefat striped mrt-admin-stations-table">
        <thead>
          <tr>
            <th>{{ adminStr(cfg, 'stationsColName') }}</th>
            <th>{{ adminStr(cfg, 'stationsColType') }}</th>
            <th>{{ adminStr(cfg, 'stationsColLat') }}</th>
            <th>{{ adminStr(cfg, 'stationsColLng') }}</th>
            <th>{{ adminStr(cfg, 'stationsColBus') }}</th>
            <th>{{ adminStr(cfg, 'stationsColOrder') }}</th>
            <th v-if="cfg.canManage"></th>
          </tr>
        </thead>
        <tbody>
          <AdminFlashRow
            v-for="st in stations"
            :key="st.id"
            :active="isFlashed(st.id)"
          >
            <td>
              <input v-if="cfg.canManage" v-model="st.title" type="text" class="regular-text" />
              <span v-else>{{ st.title }}</span>
            </td>
            <td>
              <select v-if="cfg.canManage" v-model="st.station_type">
                <option value="">—</option>
                <option value="station">{{ adminStr(cfg, 'stationsTypeStation') }}</option>
                <option value="halt">{{ adminStr(cfg, 'stationsTypeHalt') }}</option>
                <option value="depot">{{ adminStr(cfg, 'stationsTypeDepot') }}</option>
                <option value="museum">{{ adminStr(cfg, 'stationsTypeMuseum') }}</option>
              </select>
              <span v-else>{{ routePreviewTypeLabel(st.station_type, (k) => adminStr(cfg, k)) || '—' }}</span>
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
              <span v-else>{{ st.bus_suffix ? adminStr(cfg, 'yes') : '—' }}</span>
            </td>
            <td>
              <input v-if="cfg.canManage" v-model.number="st.display_order" type="number" class="small-text" />
              <span v-else>{{ st.display_order }}</span>
            </td>
            <td v-if="cfg.canManage">
              <AdminRowActions>
                <MrtButton context="admin" variant="secondary" @click="saveStationMeta(st)">
                  {{ adminStr(cfg, 'save') }}
                </MrtButton>
                <MrtButton context="admin" variant="link-delete" @click="removeStation(st)">
                  {{ adminStr(cfg, 'delete') }}
                </MrtButton>
              </AdminRowActions>
            </td>
          </AdminFlashRow>
        </tbody>
      </table>
      </AdminTableScroll>
    </AdminPanel>

    <AdminPanel v-if="sectionTab === 'routes'">
      <h2 class="screen-reader-text">{{ adminStr(cfg, 'stationsTabRoutes') }}</h2>
      <AdminInlineForm v-if="cfg.canManage">
        <input
          v-model="newRouteTitle"
          type="text"
          class="regular-text"
          :placeholder="adminStr(cfg, 'stationsNewRoute')"
        />
        <MrtButton context="admin" variant="primary" @click="addRoute">
          {{ adminStr(cfg, 'add') }}
        </MrtButton>
      </AdminInlineForm>
      <AdminEmptyState
        v-if="!routes.length"
        :title="adminStr(cfg, 'stationsEmptyRoutesTitle')"
        :message="adminStr(cfg, 'stationsEmptyRoutesMsg')"
      />
      <AdminTableScroll v-else>
      <table class="widefat striped mrt-admin-routes-table">
        <thead>
          <tr>
            <th>{{ adminStr(cfg, 'stationsColName') }}</th>
            <th>{{ adminStr(cfg, 'stationsColStations') }}</th>
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
              <AdminRowActions>
                <MrtButton v-if="cfg.canManage" context="admin" variant="secondary" @click="editRoute(route)">
                  {{ adminStr(cfg, 'edit') }}
                </MrtButton>
                <MrtButton
                  v-if="cfg.canManage"
                  context="admin"
                  variant="link-delete"
                  @click="removeRoute(route)"
                >
                  {{ adminStr(cfg, 'delete') }}
                </MrtButton>
              </AdminRowActions>
            </td>
          </tr>
        </tbody>
      </table>
      </AdminTableScroll>
    </AdminPanel>

    <AdminPanel
      v-if="sectionTab === 'routes' && editingRoute"
      class="mrt-admin-route-editor"
      :title="adminFmt(cfg, 'stationsEditRouteTitle', editingRoute.title)"
    >
      <p>
        <input v-model="editingRoute.title" type="text" class="regular-text" />
      </p>
      <p>
        <label>{{ adminStr(cfg, 'stationsRouteStart') }}</label>
        <select v-model.number="editingRoute.start_station">
          <option :value="0">—</option>
          <option v-for="st in stations" :key="st.id" :value="st.id">{{ st.title }}</option>
        </select>
        <label>{{ adminStr(cfg, 'stationsRouteEnd') }}</label>
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
        :label="adminStr(cfg, 'stationsRoutePreview')"
      />
      <ol class="mrt-admin-route-station-list">
        <li v-for="(sid, idx) in editingRoute.station_ids" :key="sid" class="mrt-admin-route-station-row">
          <span class="mrt-admin-route-station-row__name">
            {{ stations.find((s) => s.id === sid)?.title || sid }}
          </span>
          <AdminRowActions>
            <MrtButton context="admin" variant="secondary" @click="moveStation(idx, -1)">↑</MrtButton>
            <MrtButton context="admin" variant="secondary" @click="moveStation(idx, 1)">↓</MrtButton>
          </AdminRowActions>
        </li>
      </ol>
      <AdminInlineForm>
        <select v-model.number="addStationToRoute">
          <option :value="0">{{ adminStr(cfg, 'stationsAddStationPrompt') }}</option>
          <option v-for="st in stations" :key="st.id" :value="st.id">{{ st.title }}</option>
        </select>
        <MrtButton context="admin" variant="secondary" @click="appendStationToRoute">
          {{ adminStr(cfg, 'add') }}
        </MrtButton>
      </AdminInlineForm>
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="saveRoute">
          {{ adminStr(cfg, 'stationsSaveRoute') }}
        </MrtButton>
        <MrtButton context="admin" variant="secondary" @click="editingRoute = null">
          {{ adminStr(cfg, 'cancel') }}
        </MrtButton>
      </AdminFormActions>
    </AdminPanel>
    </AdminLoadState>
  </div>
</template>
