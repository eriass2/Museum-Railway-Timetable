<script setup lang="ts">
import { RouterLink } from 'vue-router';
import StationEditorFields from './StationEditorFields.vue';
import {
  AdminEmptyState,
  AdminEntityEditorShell,
  AdminFlashRow,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from '../ui';
import { routePreviewTypeLabel } from '../../utils/stations-routes/routePreviewNodes';
import { adminFmt, adminStr } from '../../utils/adminLabels';
import {
  formatStationPriceZones,
  stationMissingPriceZone,
} from '../../utils/stations-routes/stationPriceZones';
import { adminConfig } from '../../types';
import type { StationRow } from '../../types';

export type StationsPanelView = 'list' | 'create' | 'edit';

defineProps<{
  stations: StationRow[];
  priceZoneOptions: number[];
  isFlashed: (id: number) => boolean;
  viewMode: StationsPanelView;
}>();

const newStation = defineModel<StationRow>('newStation', { required: true });
const editingStation = defineModel<StationRow | null>('editingStation', { required: true });

const emit = defineEmits<{
  add: [];
  back: [];
  edit: [station: StationRow];
  remove: [station: StationRow];
  save: [];
  'start-create': [];
}>();

const cfg = adminConfig();

function stationTypeLabel(stationType: string): string {
  return routePreviewTypeLabel(stationType, (key) => adminStr(cfg, key)) || '—';
}
</script>

<template>
  <AdminPanel>
    <h2 class="screen-reader-text">{{ adminStr(cfg, 'stationsTabStations') }}</h2>
    <p v-if="viewMode === 'list'" class="description">
      {{ adminStr(cfg, 'stationsZonesHint') }}
      <RouterLink :to="{ path: '/help', query: { section: 'price-zones' } }">
        {{ adminStr(cfg, 'stationsZonesHelpLink') }}
      </RouterLink>
    </p>

    <template v-if="viewMode === 'list'">
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
              <th>{{ adminStr(cfg, 'stationsColZones') }}</th>
              <th>{{ adminStr(cfg, 'stationsColOrder') }}</th>
              <th v-if="cfg.canManage"></th>
            </tr>
          </thead>
          <tbody>
            <AdminFlashRow v-for="st in stations" :key="st.id" :active="isFlashed(st.id)">
              <td>{{ st.title }}</td>
              <td>{{ stationTypeLabel(st.station_type) }}</td>
              <td :class="{ 'mrt-admin-station-zones--missing': stationMissingPriceZone(st) }">
                {{ formatStationPriceZones(st.price_zones) || '—' }}
                <span v-if="stationMissingPriceZone(st)" class="mrt-admin-station-missing-zone">
                  ({{ adminStr(cfg, 'stationsMissingZoneBadge') }})
                </span>
              </td>
              <td>{{ st.display_order }}</td>
              <td v-if="cfg.canManage">
                <AdminRowActions>
                  <MrtButton context="admin" variant="secondary" @click="emit('edit', st)">
                    {{ adminStr(cfg, 'edit') }}
                  </MrtButton>
                  <MrtButton context="admin" variant="link-delete" @click="emit('remove', st)">
                    {{ adminStr(cfg, 'delete') }}
                  </MrtButton>
                </AdminRowActions>
              </td>
            </AdminFlashRow>
          </tbody>
        </table>
      </AdminTableScroll>
      <AdminFormActions v-if="cfg.canManage">
        <MrtButton context="admin" variant="primary" @click="emit('start-create')">
          {{ adminStr(cfg, 'stationsNewStation') }}
        </MrtButton>
      </AdminFormActions>
    </template>

    <AdminEntityEditorShell
      v-else-if="viewMode === 'create'"
      :heading="adminStr(cfg, 'stationsNewStation')"
      :submit-label="adminStr(cfg, 'add')"
      @back="emit('back')"
      @submit="emit('add')"
    >
      <StationEditorFields
        v-model="newStation"
        id-prefix="mrt-new-st"
        :price-zone-options="priceZoneOptions"
        :show-train-change="true"
      />
    </AdminEntityEditorShell>

    <AdminEntityEditorShell
      v-else-if="viewMode === 'edit' && editingStation"
      :heading="adminFmt(cfg, 'stationsEditStationTitle', editingStation.title)"
      :submit-label="adminStr(cfg, 'save')"
      @back="emit('back')"
      @submit="emit('save')"
    >
      <StationEditorFields
        v-model="editingStation"
        id-prefix="mrt-edit-st"
        :price-zone-options="priceZoneOptions"
        :show-train-change="true"
      />
    </AdminEntityEditorShell>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-station-zones--missing {
  background: #fcf9e8;
}

.mrt-admin-station-missing-zone {
  color: #996800;
}
</style>
