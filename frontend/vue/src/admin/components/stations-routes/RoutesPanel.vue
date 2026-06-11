<script setup lang="ts">
import {
  AdminEmptyState,
  AdminEntityEditorShell,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from '../ui';
import RouteEditorFields from './RouteEditorFields.vue';
import RoutePreview from './RoutePreview.vue';
import { useRouteStationOrderHandlers } from '../../composables/stations-routes/useRouteStationOrderHandlers';
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

const { onMove: onNewRouteMove, onRemove: onNewRouteRemove } =
  useRouteStationOrderHandlers(newRoute);
const { onMove: onEditRouteMove, onRemove: onEditRouteRemove } =
  useRouteStationOrderHandlers(editingRoute);
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

    <AdminEntityEditorShell
      v-else-if="viewMode === 'create'"
      :heading="adminStr(cfg, 'stationsNewRoute')"
      :submit-label="adminStr(cfg, 'add')"
      @back="emit('back')"
      @submit="emit('add')"
    >
      <RouteEditorFields
        v-model="newRoute"
        :stations="stations"
        :station-title="stationTitle"
        id-prefix="mrt-new-route"
        title-input-id="mrt-new-route-title"
        show-create-hint
        @move="onNewRouteMove"
        @remove="onNewRouteRemove"
      />
    </AdminEntityEditorShell>

    <AdminEntityEditorShell
      v-else-if="viewMode === 'edit' && editingRoute"
      :heading="adminFmt(cfg, 'stationsEditRouteTitle', editingRoute.title)"
      :submit-label="adminStr(cfg, 'stationsSaveRoute')"
      @back="emit('back')"
      @submit="emit('save')"
    >
      <RouteEditorFields
        v-model="editingRoute"
        :stations="stations"
        :station-title="stationTitle"
        id-prefix="mrt-route"
        title-input-id="mrt-route-title"
        @move="onEditRouteMove"
        @remove="onEditRouteRemove"
      />
    </AdminEntityEditorShell>
  </AdminPanel>
</template>
