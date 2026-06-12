<script setup lang="ts">
import { computed, ref } from 'vue';
import { AdminInlineForm, AdminRowActions, MrtButton } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import {
  appendRouteStation,
  routeStationRoleFor,
  syncRouteTermini,
} from '../../utils/stations-routes/routeStationEditor';
import { adminConfig } from '../../types';
import type { RouteRow, StationRow } from '../../types';

const route = defineModel<RouteRow>({ required: true });
const props = defineProps<{
  stations: StationRow[];
  stationTitle: (stationId: number) => string;
  idPrefix: string;
}>();

const emit = defineEmits<{
  move: [idx: number, dir: -1 | 1];
  remove: [idx: number];
}>();

const cfg = adminConfig();
const addStationId = ref(0);

const availableStations = computed(() =>
  props.stations.filter((s) => !route.value.station_ids.includes(s.id)),
);

const orderedStationRows = computed(() =>
  route.value.station_ids.map((sid, idx) => ({
    sid,
    idx,
    role: routeStationRoleFor(route.value, sid),
  })),
);

function onAppend() {
  if (!addStationId.value) return;
  route.value = syncRouteTermini({
    ...route.value,
    station_ids: appendRouteStation(route.value.station_ids, addStationId.value),
  });
  addStationId.value = 0;
}
</script>

<template>
  <div class="mrt-admin-route-editor__section">
    <h3 class="mrt-admin-route-editor__heading">{{ adminStr(cfg, 'stationsRouteOrderLegend') }}</h3>
    <p class="description">{{ adminStr(cfg, 'stationsRouteOrderHint') }}</p>
    <p v-if="!route.station_ids.length" class="description mrt-admin-route-editor__empty">
      {{ adminStr(cfg, 'stationsRouteEmptyStations') }}
    </p>
    <ol v-else class="mrt-admin-route-station-list">
      <li
        v-for="row in orderedStationRows"
        :key="`${idPrefix}-${row.sid}`"
        class="mrt-admin-route-station-row"
      >
        <span class="mrt-admin-route-station-row__order">{{ row.idx + 1 }}</span>
        <span class="mrt-admin-route-station-row__name">{{ stationTitle(row.sid) }}</span>
        <span v-if="row.role" class="mrt-admin-route-station-row__badges">
          <span
            v-if="row.role === 'start' || row.role === 'both'"
            class="mrt-admin-route-station-row__badge mrt-admin-route-station-row__badge--start"
          >
            {{ adminStr(cfg, 'routePreviewStart') }}
          </span>
          <span
            v-if="row.role === 'end' || row.role === 'both'"
            class="mrt-admin-route-station-row__badge mrt-admin-route-station-row__badge--end"
          >
            {{ adminStr(cfg, 'routePreviewEnd') }}
          </span>
        </span>
        <AdminRowActions>
          <MrtButton
            context="admin"
            variant="secondary"
            :disabled="row.idx === 0"
            :aria-label="adminStr(cfg, 'stationsRouteMoveUp')"
            @click="emit('move', row.idx, -1)"
          >
            ↑
          </MrtButton>
          <MrtButton
            context="admin"
            variant="secondary"
            :disabled="row.idx === route.station_ids.length - 1"
            :aria-label="adminStr(cfg, 'stationsRouteMoveDown')"
            @click="emit('move', row.idx, 1)"
          >
            ↓
          </MrtButton>
          <MrtButton
            context="admin"
            variant="link-delete"
            :aria-label="adminStr(cfg, 'stationsRouteRemoveStation')"
            @click="emit('remove', row.idx)"
          >
            ×
          </MrtButton>
        </AdminRowActions>
      </li>
    </ol>
    <AdminInlineForm class="mrt-admin-route-editor__add">
      <select v-model.number="addStationId">
        <option :value="0">{{ adminStr(cfg, 'stationsAddStationPrompt') }}</option>
        <option v-for="st in availableStations" :key="`${idPrefix}-add-${st.id}`" :value="st.id">
          {{ st.title }}
        </option>
      </select>
      <MrtButton context="admin" variant="secondary" @click="onAppend">
        {{ adminStr(cfg, 'add') }}
      </MrtButton>
    </AdminInlineForm>
  </div>
</template>

<style scoped>
.mrt-admin-route-editor__section {
  margin-bottom: 16px;
}

.mrt-admin-route-editor__section:last-child {
  margin-bottom: 0;
}

.mrt-admin-route-editor__heading {
  margin: 0 0 4px;
  font-size: 14px;
}

.mrt-admin-route-editor__empty {
  margin-top: 0;
}

.mrt-admin-route-station-list {
  list-style: none;
  margin: 8px 0 0;
  padding: 0;
  max-width: 36em;
}

.mrt-admin-route-station-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 0;
  border-bottom: 1px solid #dcdcde;
}

.mrt-admin-route-station-row:last-child {
  border-bottom: none;
}

.mrt-admin-route-station-row__order {
  flex: 0 0 1.5em;
  color: #646970;
  font-variant-numeric: tabular-nums;
  text-align: right;
}

.mrt-admin-route-station-row__name {
  flex: 1;
  min-width: 0;
}

.mrt-admin-route-station-row__badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.mrt-admin-route-station-row__badge {
  padding: 1px 6px;
  border-radius: 3px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  line-height: 1.4;
}

.mrt-admin-route-station-row__badge--start {
  border: 1px solid #2271b1;
  background: #f0f6fc;
  color: #1d4f8c;
}

.mrt-admin-route-station-row__badge--end {
  border: 1px solid #3a6b1f;
  background: #f0f6eb;
  color: #2f5c17;
}

.mrt-admin-route-editor__add {
  margin-top: 12px;
}

@media (max-width: 782px) {
  .mrt-admin-route-station-list {
    max-width: none;
  }

  .mrt-admin-route-editor__add :deep(select),
  .mrt-admin-route-editor__add :deep(.button) {
    width: 100%;
  }
}
</style>
