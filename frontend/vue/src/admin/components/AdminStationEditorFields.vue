<script setup lang="ts">
import { AdminDisclosure } from './ui';
import StationTrainChangeEditor from './StationTrainChangeEditor.vue';
import { adminStr } from '../utils/adminLabels';
import {
  stationHasPriceZone,
  toggleStationPriceZone,
} from '../utils/stationPriceZones';
import { adminConfig } from '../types';
import type { StationRow } from '../types';

defineProps<{
  priceZoneOptions: number[];
  idPrefix: string;
  showTrainChange?: boolean;
}>();

const station = defineModel<StationRow>({ required: true });

const cfg = adminConfig();
</script>

<template>
  <div class="mrt-admin-station-editor__fields">
    <p>
      <label :for="`${idPrefix}-title`">{{ adminStr(cfg, 'stationsColName') }}</label>
      <input
        :id="`${idPrefix}-title`"
        v-model="station.title"
        type="text"
        class="regular-text"
        :placeholder="adminStr(cfg, 'stationsNewStation')"
      />
    </p>
    <p>
      <label :for="`${idPrefix}-type`">{{ adminStr(cfg, 'stationsColType') }}</label>
      <select :id="`${idPrefix}-type`" v-model="station.station_type">
        <option value="">—</option>
        <option value="station">{{ adminStr(cfg, 'stationsTypeStation') }}</option>
        <option value="halt">{{ adminStr(cfg, 'stationsTypeHalt') }}</option>
        <option value="depot">{{ adminStr(cfg, 'stationsTypeDepot') }}</option>
        <option value="museum">{{ adminStr(cfg, 'stationsTypeMuseum') }}</option>
      </select>
    </p>
    <p>
      <label :for="`${idPrefix}-lat`">{{ adminStr(cfg, 'stationsColLat') }}</label>
      <input
        :id="`${idPrefix}-lat`"
        v-model="station.lat"
        type="text"
        class="small-text"
        placeholder="57.48"
      />
    </p>
    <p>
      <label :for="`${idPrefix}-lng`">{{ adminStr(cfg, 'stationsColLng') }}</label>
      <input
        :id="`${idPrefix}-lng`"
        v-model="station.lng"
        type="text"
        class="small-text"
        placeholder="15.82"
      />
    </p>
    <p>
      <label>
        <input v-model="station.bus_suffix" type="checkbox" />
        {{ adminStr(cfg, 'stationsColBus') }}
      </label>
    </p>
    <p>
      <span class="label">{{ adminStr(cfg, 'stationsColZones') }}</span>
      <span class="mrt-admin-zone-picks">
        <label
          v-for="zone in priceZoneOptions"
          :key="`${idPrefix}-${zone}`"
          class="mrt-admin-zone-picks__item"
        >
          <input
            type="checkbox"
            :checked="stationHasPriceZone(station, zone)"
            @change="toggleStationPriceZone(station, zone)"
          />
          {{ zone }}
        </label>
      </span>
    </p>
    <p>
      <label :for="`${idPrefix}-order`">{{ adminStr(cfg, 'stationsColOrder') }}</label>
      <input
        :id="`${idPrefix}-order`"
        v-model.number="station.display_order"
        type="number"
        class="small-text"
      />
    </p>
    <AdminDisclosure
      v-if="showTrainChange !== false"
      :summary="adminStr(cfg, 'stationsTrainChangeSummary', 'Tågbyte')"
    >
      <StationTrainChangeEditor :station="station" />
    </AdminDisclosure>
  </div>
</template>

<style scoped>
.mrt-admin-station-editor__fields p {
  margin: 0 0 12px;
}

.mrt-admin-station-editor__fields .label {
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
}
</style>
