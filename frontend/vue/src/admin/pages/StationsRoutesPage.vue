<script setup lang="ts">
import AdminLoadState from '../components/AdminLoadState.vue';
import RoutesPanel from '../components/stations-routes/RoutesPanel.vue';
import StationsPanel from '../components/stations-routes/StationsPanel.vue';
import { AdminStatusMessage } from '../components/ui';
import { useStationsRoutesPage } from '../composables/stations-routes/useStationsRoutesPage';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminStr } from '../utils/adminLabels';

const { isMobile } = useMobileAdmin();
const {
  cfg,
  stations,
  visibleStations,
  priceZoneOptions,
  showMissingZonesOnly,
  routes,
  saveMsg,
  newStation,
  newRoute,
  sectionTab,
  editingStation,
  editingRoute,
  stationsView,
  routesView,
  loading,
  error,
  load,
  stationsById,
  isFlashed,
  stationTitle,
  addStation,
  addRoute,
  editStation,
  editRoute,
  saveEditingStation,
  saveRoute,
  startCreateStation,
  startCreateRoute,
  requestBackToStationsList,
  requestBackToRoutesList,
  removeStation,
  removeRoute,
} = useStationsRoutesPage();
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'stationsTitle') }}</h1>
    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'stationsLoading')"
      @retry="load"
    >
      <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
      <nav
        class="nav-tab-wrapper mrt-admin-section-nav"
        :aria-label="adminStr(cfg, 'stationsNavAria')"
      >
        <a
          href="#"
          class="nav-tab"
          :class="{ 'nav-tab-active': sectionTab === 'stations' }"
          @click.prevent="sectionTab = 'stations'"
        >
          {{ adminStr(cfg, 'stationsTabStations') }}
        </a>
        <a
          href="#"
          class="nav-tab"
          :class="{ 'nav-tab-active': sectionTab === 'routes' }"
          @click.prevent="sectionTab = 'routes'"
        >
          {{ adminStr(cfg, 'stationsTabRoutes') }}
        </a>
      </nav>

      <div
        v-if="sectionTab === 'stations' && cfg.canManage && stationsView === 'list'"
        class="mrt-admin-stations-toolbar"
      >
        <label class="mrt-admin-stations-filter">
          <input v-model="showMissingZonesOnly" type="checkbox" />
          {{ adminStr(cfg, 'stationsFilterMissingZones') }}
        </label>
      </div>

      <StationsPanel
        v-if="sectionTab === 'stations'"
        v-model:new-station="newStation"
        v-model:editing-station="editingStation"
        :stations="visibleStations"
        :price-zone-options="priceZoneOptions"
        :is-flashed="isFlashed"
        :view-mode="stationsView"
        @add="addStation"
        @edit="editStation"
        @save="saveEditingStation"
        @start-create="startCreateStation"
        @back="requestBackToStationsList"
        @remove="removeStation"
      />

      <RoutesPanel
        v-if="sectionTab === 'routes'"
        v-model:new-route="newRoute"
        v-model:editing-route="editingRoute"
        :routes="routes"
        :stations="stations"
        :stations-by-id="stationsById"
        :station-title="stationTitle"
        :view-mode="routesView"
        @add="addRoute"
        @edit="editRoute"
        @save="saveRoute"
        @start-create="startCreateRoute"
        @back="requestBackToRoutesList"
        @remove="removeRoute"
      />
    </AdminLoadState>
  </div>
</template>

<style scoped>
.mrt-admin-stations-toolbar {
  margin: 0 0 12px;
}

.mrt-admin-stations-filter {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
</style>
