<script setup lang="ts">
import {
  AdminBackNav,
  AdminDisclosure,
  AdminEmptyState,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from '../ui';
import RoutePreview from './RoutePreview.vue';
import RouteStationOrderEditor from './RouteStationOrderEditor.vue';
import { moveRouteStation, removeRouteStation } from '../../utils/stations-routes/routeStationEditor';
import { adminFmt, adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import type { RouteRow, StationRow } from '../../types';

export type RoutesPanelView = 'list' | 'create' | 'edit';

defineProps<{
  routes: RouteRow[];
  stations: StationRow[];
  stationsById: Map<number, { title: string; station_type: string }>;
  stationTitle: (stationId: number) => string;
  viewMode: RoutesPanelView;
}>();

const newRoute = defineModel<RouteRow>('newRoute', { required: true });
const editingRoute = defineModel<RouteRow | null>('editingRoute', { required: true });

const emit = defineEmits<{
  add: [];
  back: [];
  edit: [route: RouteRow];
  save: [];
  'start-create': [];
  remove: [route: RouteRow];
}>();

const cfg = adminConfig();

function onNewRouteMove(idx: number, dir: -1 | 1) {
  newRoute.value.station_ids = moveRouteStation(newRoute.value.station_ids, idx, dir);
}

function onNewRouteRemove(idx: number) {
  Object.assign(newRoute.value, removeRouteStation(newRoute.value, idx));
}

function onEditRouteMove(idx: number, dir: -1 | 1) {
  if (!editingRoute.value) return;
  editingRoute.value.station_ids = moveRouteStation(editingRoute.value.station_ids, idx, dir);
}

function onEditRouteRemove(idx: number) {
  if (!editingRoute.value) return;
  Object.assign(editingRoute.value, removeRouteStation(editingRoute.value, idx));
}
</script>

<template>
  <AdminPanel>
    <h2 class="screen-reader-text">{{ adminStr(cfg, 'stationsTabRoutes') }}</h2>

    <template v-if="viewMode === 'list'">
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
                  <MrtButton
                    v-if="cfg.canManage"
                    context="admin"
                    variant="secondary"
                    @click="emit('edit', route)"
                  >
                    {{ adminStr(cfg, 'edit') }}
                  </MrtButton>
                  <MrtButton
                    v-if="cfg.canManage"
                    context="admin"
                    variant="link-delete"
                    @click="emit('remove', route)"
                  >
                    {{ adminStr(cfg, 'delete') }}
                  </MrtButton>
                </AdminRowActions>
              </td>
            </tr>
          </tbody>
        </table>
      </AdminTableScroll>
      <AdminFormActions v-if="cfg.canManage">
        <MrtButton context="admin" variant="primary" @click="emit('start-create')">
          {{ adminStr(cfg, 'stationsNewRoute') }}
        </MrtButton>
      </AdminFormActions>
    </template>

    <template v-else-if="viewMode === 'create'">
      <AdminBackNav @back="emit('back')" />
      <h3 class="mrt-admin-route-editor__heading">{{ adminStr(cfg, 'stationsNewRoute') }}</h3>
      <div class="mrt-admin-route-editor__section">
        <label class="mrt-admin-route-editor__label" for="mrt-new-route-title">
          {{ adminStr(cfg, 'stationsRouteNameLabel') }}
        </label>
        <input
          id="mrt-new-route-title"
          v-model="newRoute.title"
          type="text"
          class="regular-text"
          :placeholder="adminStr(cfg, 'stationsNewRoute')"
        />
      </div>
      <AdminDisclosure :summary="adminStr(cfg, 'stationsRouteCreateMoreFields')">
        <p class="description">{{ adminStr(cfg, 'stationsRouteOrderHint') }}</p>
      </AdminDisclosure>
      <RouteStationOrderEditor
        v-model="newRoute"
        :stations="stations"
        :station-title="stationTitle"
        id-prefix="mrt-new-route"
        @move="onNewRouteMove"
        @remove="onNewRouteRemove"
      />
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="emit('add')">
          {{ adminStr(cfg, 'add') }}
        </MrtButton>
        <MrtButton context="admin" variant="secondary" @click="emit('back')">
          {{ adminStr(cfg, 'cancel') }}
        </MrtButton>
      </AdminFormActions>
    </template>

    <template v-else-if="viewMode === 'edit' && editingRoute">
      <AdminBackNav @back="emit('back')" />
      <h3 class="mrt-admin-route-editor__heading">
        {{ adminFmt(cfg, 'stationsEditRouteTitle', editingRoute.title) }}
      </h3>
      <div class="mrt-admin-route-editor__section">
        <label class="mrt-admin-route-editor__label" for="mrt-route-title">
          {{ adminStr(cfg, 'stationsRouteNameLabel') }}
        </label>
        <input id="mrt-route-title" v-model="editingRoute.title" type="text" class="regular-text" />
      </div>

      <RouteStationOrderEditor
        v-model="editingRoute"
        :stations="stations"
        :station-title="stationTitle"
        id-prefix="mrt-route"
        @move="onEditRouteMove"
        @remove="onEditRouteRemove"
      />

      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="emit('save')">
          {{ adminStr(cfg, 'stationsSaveRoute') }}
        </MrtButton>
        <MrtButton context="admin" variant="secondary" @click="emit('back')">
          {{ adminStr(cfg, 'cancel') }}
        </MrtButton>
      </AdminFormActions>
    </template>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-route-editor__heading {
  margin: 0 0 12px;
  font-size: 14px;
  font-weight: 600;
}

.mrt-admin-route-editor__section {
  margin-bottom: 12px;
}

.mrt-admin-route-editor__label {
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
}
</style>
