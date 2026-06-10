<script setup lang="ts">
import { RouterLink } from 'vue-router';
import StationEditorFields from './StationEditorFields.vue';
import {
  AdminBackNav,
  AdminEmptyState,
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
              <td>{{
                routePreviewTypeLabel(st.station_type, (k) => adminStr(cfg, k)) || '—'
              }}</td>
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

    <template v-else-if="viewMode === 'create'">
      <AdminBackNav @back="emit('back')" />
      <h3 class="mrt-admin-station-editor__heading">{{ adminStr(cfg, 'stationsNewStation') }}</h3>
      <StationEditorFields
        v-model="newStation"
        id-prefix="mrt-new-st"
        :price-zone-options="priceZoneOptions"
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

    <template v-else-if="viewMode === 'edit' && editingStation">
      <AdminBackNav @back="emit('back')" />
      <h3 class="mrt-admin-station-editor__heading">
        {{ adminFmt(cfg, 'stationsEditStationTitle', editingStation.title) }}
      </h3>
      <StationEditorFields
        v-model="editingStation"
        id-prefix="mrt-edit-st"
        :price-zone-options="priceZoneOptions"
      />
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="emit('save')">
          {{ adminStr(cfg, 'save') }}
        </MrtButton>
        <MrtButton context="admin" variant="secondary" @click="emit('back')">
          {{ adminStr(cfg, 'cancel') }}
        </MrtButton>
      </AdminFormActions>
    </template>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-station-editor__heading {
  margin: 0 0 12px;
  font-size: 14px;
  font-weight: 600;
}

.mrt-admin-station-zones--missing {
  background: #fcf9e8;
}

.mrt-admin-station-missing-zone {
  color: #996800;
}
</style>
