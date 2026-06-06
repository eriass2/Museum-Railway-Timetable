<script setup lang="ts">
import {
  AdminDisclosure,
  AdminEmptyState,
  AdminFlashRow,
  AdminInlineForm,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from './ui';
import { routePreviewTypeLabel } from '../utils/routePreviewNodes';
import { adminStr } from '../utils/adminLabels';
import {
  formatStationPriceZones,
  stationHasPriceZone,
  STATION_PRICE_ZONE_OPTIONS,
  toggleStationPriceZone,
} from '../utils/stationPriceZones';
import { adminConfig } from '../types';
import type { StationRow } from '../types';

defineProps<{
  stations: StationRow[];
  isFlashed: (id: number) => boolean;
}>();

const newStation = defineModel<StationRow>('newStation', { required: true });

const emit = defineEmits<{
  add: [];
  save: [station: StationRow];
  remove: [station: StationRow];
}>();

const cfg = adminConfig();
</script>

<template>
  <AdminPanel>
    <h2 class="screen-reader-text">{{ adminStr(cfg, 'stationsTabStations') }}</h2>
    <div v-if="cfg.canManage" class="mrt-admin-station-create">
      <AdminInlineForm>
        <input
          v-model="newStation.title"
          type="text"
          class="regular-text"
          :placeholder="adminStr(cfg, 'stationsNewStation')"
        />
        <MrtButton context="admin" variant="primary" @click="emit('add')">
          {{ adminStr(cfg, 'add') }}
        </MrtButton>
      </AdminInlineForm>
      <AdminDisclosure :summary="adminStr(cfg, 'stationsCreateMoreFields')">
        <div class="mrt-admin-station-create__fields">
          <p>
            <label for="mrt-new-st-type">{{ adminStr(cfg, 'stationsColType') }}</label>
            <select id="mrt-new-st-type" v-model="newStation.station_type">
              <option value="">—</option>
              <option value="station">{{ adminStr(cfg, 'stationsTypeStation') }}</option>
              <option value="halt">{{ adminStr(cfg, 'stationsTypeHalt') }}</option>
              <option value="depot">{{ adminStr(cfg, 'stationsTypeDepot') }}</option>
              <option value="museum">{{ adminStr(cfg, 'stationsTypeMuseum') }}</option>
            </select>
          </p>
          <p>
            <label for="mrt-new-st-lat">{{ adminStr(cfg, 'stationsColLat') }}</label>
            <input id="mrt-new-st-lat" v-model="newStation.lat" type="text" class="small-text" />
          </p>
          <p>
            <label for="mrt-new-st-lng">{{ adminStr(cfg, 'stationsColLng') }}</label>
            <input id="mrt-new-st-lng" v-model="newStation.lng" type="text" class="small-text" />
          </p>
          <p>
            <label>
              <input v-model="newStation.bus_suffix" type="checkbox" />
              {{ adminStr(cfg, 'stationsColBus') }}
            </label>
          </p>
          <p>
            <span class="label">{{ adminStr(cfg, 'stationsColZones') }}</span>
            <span class="mrt-admin-zone-picks">
              <label
                v-for="zone in STATION_PRICE_ZONE_OPTIONS"
                :key="`new-${zone}`"
                class="mrt-admin-zone-picks__item"
              >
                <input
                  type="checkbox"
                  :checked="stationHasPriceZone(newStation, zone)"
                  @change="toggleStationPriceZone(newStation, zone)"
                />
                {{ zone }}
              </label>
            </span>
          </p>
          <p>
            <label for="mrt-new-st-order">{{ adminStr(cfg, 'stationsColOrder') }}</label>
            <input
              id="mrt-new-st-order"
              v-model.number="newStation.display_order"
              type="number"
              class="small-text"
            />
          </p>
        </div>
      </AdminDisclosure>
    </div>
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
            <th>{{ adminStr(cfg, 'stationsColLat') }}</th>
            <th>{{ adminStr(cfg, 'stationsColLng') }}</th>
            <th>{{ adminStr(cfg, 'stationsColBus') }}</th>
            <th>{{ adminStr(cfg, 'stationsColZones') }}</th>
            <th>{{ adminStr(cfg, 'stationsColOrder') }}</th>
            <th v-if="cfg.canManage"></th>
          </tr>
        </thead>
        <tbody>
          <AdminFlashRow v-for="st in stations" :key="st.id" :active="isFlashed(st.id)">
            <td>
              <input v-if="cfg.canManage" v-model="st.title" type="text" class="regular-text" />
              <span v-else>{{ st.title }}</span>
            </td>
            <td>
              <select v-if="cfg.canManage" v-model="st.station_type">
                <option value="">—</option>
                <option value="station">{{ adminStr(cfg, 'stationsTypeStation') }}</option>
                <option value="halt">{{ adminStr(cfg, 'stationsTypeHalt') }}</option>
                <option value="depot">{{ adminStr(cfg, 'stationsTypeDepot') }}</option>
                <option value="museum">{{ adminStr(cfg, 'stationsTypeMuseum') }}</option>
              </select>
              <span v-else>{{
                routePreviewTypeLabel(st.station_type, (k) => adminStr(cfg, k)) || '—'
              }}</span>
            </td>
            <td>
              <input
                v-if="cfg.canManage"
                v-model="st.lat"
                type="text"
                class="small-text"
                placeholder="57.48"
              />
              <span v-else>{{ st.lat || '—' }}</span>
            </td>
            <td>
              <input
                v-if="cfg.canManage"
                v-model="st.lng"
                type="text"
                class="small-text"
                placeholder="15.82"
              />
              <span v-else>{{ st.lng || '—' }}</span>
            </td>
            <td>
              <input v-if="cfg.canManage" v-model="st.bus_suffix" type="checkbox" />
              <span v-else>{{ st.bus_suffix ? adminStr(cfg, 'yes') : '—' }}</span>
            </td>
            <td>
              <div
                v-if="cfg.canManage"
                class="mrt-admin-zone-picks"
                :aria-label="adminStr(cfg, 'stationsColZones')"
              >
                <label
                  v-for="zone in STATION_PRICE_ZONE_OPTIONS"
                  :key="`${st.id}-${zone}`"
                  class="mrt-admin-zone-picks__item"
                >
                  <input
                    type="checkbox"
                    :checked="stationHasPriceZone(st, zone)"
                    @change="toggleStationPriceZone(st, zone)"
                  />
                  {{ zone }}
                </label>
              </div>
              <span v-else>{{ formatStationPriceZones(st.price_zones) }}</span>
            </td>
            <td>
              <input
                v-if="cfg.canManage"
                v-model.number="st.display_order"
                type="number"
                class="small-text"
              />
              <span v-else>{{ st.display_order }}</span>
            </td>
            <td v-if="cfg.canManage">
              <AdminRowActions>
                <MrtButton context="admin" variant="secondary" @click="emit('save', st)">
                  {{ adminStr(cfg, 'save') }}
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
  </AdminPanel>
</template>
