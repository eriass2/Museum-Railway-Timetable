<script setup lang="ts">
import {
  AdminDisclosure,
  AdminEmptyState,
  AdminPanel,
  AdminTableScroll,
} from '../ui';
import RoutePreview from './RoutePreview.vue';
import RoutesPanel from './RoutesPanel.vue';
import { lineKindLabelKey } from '../../utils/stations-routes/lineKindLabel';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import type { LineRow, RouteRow, StationRow } from '../../types';
import type { RoutesPanelView } from './RoutesPanel.vue';

defineProps<{
  lines: LineRow[];
  routes: RouteRow[];
  stations: StationRow[];
  stationsById: Map<number, { title: string; station_type: string }>;
  stationTitle: (stationId: number) => string;
  routesView: RoutesPanelView;
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

function junctionLabel(line: LineRow): string {
  if (line.junction_station_name) {
    return line.junction_station_name;
  }
  if (line.kind === 'pattern') {
    return '—';
  }
  return '—';
}
</script>

<template>
  <AdminPanel>
    <h2 class="screen-reader-text">{{ adminStr(cfg, 'stationsTabLines') }}</h2>
    <p class="description mrt-admin-lines-help">{{ adminStr(cfg, 'stationsLinesHelp') }}</p>

    <template v-if="routesView === 'list'">
      <AdminEmptyState
        v-if="!lines.length"
        :title="adminStr(cfg, 'stationsEmptyLinesTitle')"
        :message="adminStr(cfg, 'stationsEmptyLinesMsg')"
      />
      <AdminTableScroll v-else>
        <table class="widefat striped mrt-admin-lines-table">
          <thead>
            <tr>
              <th>{{ adminStr(cfg, 'stationsColName') }}</th>
              <th>{{ adminStr(cfg, 'stationsColLineKind') }}</th>
              <th>{{ adminStr(cfg, 'stationsColJunction') }}</th>
              <th>{{ adminStr(cfg, 'stationsColStations') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="line in lines" :key="line.code">
              <td>
                <span class="mrt-admin-line-title">{{ line.title }}</span>
                <span class="mrt-admin-line-code">{{ line.code }}</span>
              </td>
              <td>
                {{ adminStr(cfg, lineKindLabelKey(line.kind)) || line.kind || '—' }}
                <span v-if="line.bidirectional" class="mrt-admin-line-meta">
                  ({{ adminStr(cfg, 'stationsLinesBidirectional') }})
                </span>
              </td>
              <td>{{ junctionLabel(line) }}</td>
              <td>
                <RoutePreview
                  :station-ids="line.station_ids"
                  :stations-by-id="stationsById"
                  :start-station-id="line.start_station"
                  :end-station-id="line.end_station"
                  compact
                />
              </td>
            </tr>
          </tbody>
        </table>
      </AdminTableScroll>

      <AdminDisclosure
        v-if="cfg.isDevMode && routes.length"
        :summary="adminStr(cfg, 'stationsLegacyRoutesSummary')"
        class="mrt-admin-lines-legacy"
      >
        <p class="description">{{ adminStr(cfg, 'stationsLegacyRoutesHint') }}</p>
        <RoutesPanel
          v-model:new-route="newRoute"
          v-model:editing-route="editingRoute"
          :routes="routes"
          :stations="stations"
          :stations-by-id="stationsById"
          :station-title="stationTitle"
          view-mode="list"
          @add="emit('add')"
          @edit="emit('edit', $event)"
          @save="emit('save')"
          @start-create="emit('start-create')"
          @back="emit('back')"
          @remove="emit('remove', $event)"
        />
      </AdminDisclosure>
    </template>

    <RoutesPanel
      v-else
      v-model:new-route="newRoute"
      v-model:editing-route="editingRoute"
      :routes="routes"
      :stations="stations"
      :stations-by-id="stationsById"
      :station-title="stationTitle"
      :view-mode="routesView"
      @add="emit('add')"
      @edit="emit('edit', $event)"
      @save="emit('save')"
      @start-create="emit('start-create')"
      @back="emit('back')"
      @remove="emit('remove', $event)"
    />
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-lines-help {
  margin: 0 0 12px;
}

.mrt-admin-line-title {
  display: block;
  font-weight: 600;
}

.mrt-admin-line-code {
  display: block;
  color: #646970;
  font-size: 12px;
}

.mrt-admin-line-meta {
  display: block;
  color: #646970;
  font-size: 12px;
}

.mrt-admin-lines-legacy {
  margin-top: 20px;
}
</style>
