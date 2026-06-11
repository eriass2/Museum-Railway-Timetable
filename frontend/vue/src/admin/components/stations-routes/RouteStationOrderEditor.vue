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
