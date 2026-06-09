<script setup lang="ts">
import { computed, ref } from 'vue';
import { AdminInlineForm, AdminRowActions, MrtButton } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { appendRouteStation, routeStationRoleFor } from '../../utils/stations-routes/routeStationEditor';
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

function onAppend() {
  if (!addStationId.value) return;
  route.value.station_ids = appendRouteStation(route.value.station_ids, addStationId.value);
  addStationId.value = 0;
}
</script>

<template>
  <fieldset class="mrt-admin-route-editor__section mrt-admin-route-editor__endpoints">
    <legend>{{ adminStr(cfg, 'stationsRouteEndpointsLegend') }}</legend>
    <p class="description">{{ adminStr(cfg, 'stationsRouteEndpointsHint') }}</p>
    <div class="mrt-admin-route-editor__endpoint-fields">
      <label class="mrt-admin-route-editor__label" :for="`${idPrefix}-start`">
        {{ adminStr(cfg, 'stationsRouteStart') }}
      </label>
      <select :id="`${idPrefix}-start`" v-model.number="route.start_station">
        <option :value="0">—</option>
        <option v-for="sid in route.station_ids" :key="`${idPrefix}-start-${sid}`" :value="sid">
          {{ stationTitle(sid) }}
        </option>
      </select>
      <label class="mrt-admin-route-editor__label" :for="`${idPrefix}-end`">
        {{ adminStr(cfg, 'stationsRouteEnd') }}
      </label>
      <select :id="`${idPrefix}-end`" v-model.number="route.end_station">
        <option :value="0">—</option>
        <option v-for="sid in route.station_ids" :key="`${idPrefix}-end-${sid}`" :value="sid">
          {{ stationTitle(sid) }}
        </option>
      </select>
    </div>
  </fieldset>

  <div class="mrt-admin-route-editor__section">
    <h3 class="mrt-admin-route-editor__heading">{{ adminStr(cfg, 'stationsRouteOrderLegend') }}</h3>
    <p class="description">{{ adminStr(cfg, 'stationsRouteOrderHint') }}</p>
    <p v-if="!route.station_ids.length" class="description mrt-admin-route-editor__empty">
      {{ adminStr(cfg, 'stationsRouteEmptyStations') }}
    </p>
    <ol v-else class="mrt-admin-route-station-list">
      <li
        v-for="(sid, idx) in route.station_ids"
        :key="`${idPrefix}-${sid}`"
        class="mrt-admin-route-station-row"
      >
        <span class="mrt-admin-route-station-row__order">{{ idx + 1 }}</span>
        <span class="mrt-admin-route-station-row__name">{{ stationTitle(sid) }}</span>
        <span v-if="routeStationRoleFor(route, sid)" class="mrt-admin-route-station-row__badges">
          <span
            v-if="
              routeStationRoleFor(route, sid) === 'start' ||
              routeStationRoleFor(route, sid) === 'both'
            "
            class="mrt-admin-route-station-row__badge mrt-admin-route-station-row__badge--start"
          >
            {{ adminStr(cfg, 'routePreviewStart') }}
          </span>
          <span
            v-if="
              routeStationRoleFor(route, sid) === 'end' ||
              routeStationRoleFor(route, sid) === 'both'
            "
            class="mrt-admin-route-station-row__badge mrt-admin-route-station-row__badge--end"
          >
            {{ adminStr(cfg, 'routePreviewEnd') }}
          </span>
        </span>
        <AdminRowActions>
          <MrtButton
            context="admin"
            variant="secondary"
            :disabled="idx === 0"
            :aria-label="adminStr(cfg, 'stationsRouteMoveUp')"
            @click="emit('move', idx, -1)"
          >
            ↑
          </MrtButton>
          <MrtButton
            context="admin"
            variant="secondary"
            :disabled="idx === route.station_ids.length - 1"
            :aria-label="adminStr(cfg, 'stationsRouteMoveDown')"
            @click="emit('move', idx, 1)"
          >
            ↓
          </MrtButton>
          <MrtButton
            context="admin"
            variant="link-delete"
            :aria-label="adminStr(cfg, 'stationsRouteRemoveStation')"
            @click="emit('remove', idx)"
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
