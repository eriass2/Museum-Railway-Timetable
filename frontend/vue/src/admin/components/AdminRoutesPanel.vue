<script setup lang="ts">
import {
  AdminDisclosure,
  AdminEmptyState,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from './ui';
import RoutePreview from './RoutePreview.vue';
import RouteStationOrderEditor from './RouteStationOrderEditor.vue';
import { moveRouteStation, removeRouteStation } from '../utils/routeStationEditor';
import { adminFmt, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';
import type { RouteRow, StationRow } from '../types';

defineProps<{
  routes: RouteRow[];
  stations: StationRow[];
  stationsById: Map<number, { title: string; station_type: string }>;
  stationTitle: (stationId: number) => string;
}>();

const newRoute = defineModel<RouteRow>('newRoute', { required: true });
const editingRoute = defineModel<RouteRow | null>('editingRoute', { required: true });

const emit = defineEmits<{
  add: [];
  edit: [route: RouteRow];
  save: [];
  cancelEdit: [];
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
    <div v-if="cfg.canManage" class="mrt-admin-route-create">
      <AdminInlineForm>
        <input
          v-model="newRoute.title"
          type="text"
          class="regular-text"
          :placeholder="adminStr(cfg, 'stationsNewRoute')"
        />
        <MrtButton context="admin" variant="primary" @click="emit('add')">
          {{ adminStr(cfg, 'add') }}
        </MrtButton>
      </AdminInlineForm>
      <AdminDisclosure :summary="adminStr(cfg, 'stationsRouteCreateMoreFields')">
        <RouteStationOrderEditor
          v-model="newRoute"
          :stations="stations"
          :station-title="stationTitle"
          id-prefix="mrt-new-route"
          @move="onNewRouteMove"
          @remove="onNewRouteRemove"
        />
      </AdminDisclosure>
    </div>
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
  </AdminPanel>

  <AdminPanel
    v-if="editingRoute"
    class="mrt-admin-route-editor"
    :title="adminFmt(cfg, 'stationsEditRouteTitle', editingRoute.title)"
  >
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
      <MrtButton context="admin" variant="secondary" @click="emit('cancelEdit')">
        {{ adminStr(cfg, 'cancel') }}
      </MrtButton>
    </AdminFormActions>
  </AdminPanel>
</template>
