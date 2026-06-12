<script setup lang="ts">
import { computed } from 'vue';
import LinesPanel from '../components/stations-routes/LinesPanel.vue';
import RoutesPanel from '../components/stations-routes/RoutesPanel.vue';
import StationsPanel from '../components/stations-routes/StationsPanel.vue';
import { MrtAlert, MrtAsyncState } from '../components/ui';
import { useStationsRoutesPage } from '../composables/stations-routes/useStationsRoutesPage';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import { adminStr } from '../utils/adminLabels';
import { buildStationsRoutesSectionTabs } from '../utils/stations-routes/stationsRoutesSectionTabs';

const { isMobile } = useMobileAdmin();
const {
  cfg,
  stations,
  visibleStations,
  priceZoneOptions,
  showMissingZonesOnly,
  routes,
  lines,
  hasLineRegistry,
  saveMsg,
  newStation,
  newRoute,
  sectionTab,
  editingStation,
  editingRoute,
  stationsView,
  routesView,
  linesView,
  editingLine,
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
  requestBackToLinesList,
  editLine,
  saveLine,
  removeStation,
  removeRoute,
} = useStationsRoutesPage();

const sectionTabs = computed(() => buildStationsRoutesSectionTabs(cfg, hasLineRegistry.value));
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{
      hasLineRegistry ? adminStr(cfg, 'stationsTitleLines') : adminStr(cfg, 'stationsTitle')
    }}</h1>
    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'stationsLoading')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
      @retry="load"
    >
      <MrtAlert v-if="saveMsg" context="admin" variant="success">{{ saveMsg }}</MrtAlert>
      <nav
        class="nav-tab-wrapper mrt-admin-section-nav"
        :aria-label="adminStr(cfg, 'stationsNavAria')"
      >
        <a
          v-for="tab in sectionTabs"
          :key="tab.id"
          href="#"
          class="nav-tab"
          :class="{ 'nav-tab-active': sectionTab === tab.id }"
          @click.prevent="sectionTab = tab.id"
        >
          {{ tab.label }}
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

      <LinesPanel
        v-if="sectionTab === 'lines'"
        v-model:editing-line="editingLine"
        :lines="lines"
        :stations-by-id="stationsById"
        :lines-view="linesView"
        @edit-line="editLine"
        @save-line="saveLine"
        @back-line="requestBackToLinesList"
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
    </MrtAsyncState>
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

.mrt-admin-section-nav {
  margin-bottom: 12px;
}
</style>
