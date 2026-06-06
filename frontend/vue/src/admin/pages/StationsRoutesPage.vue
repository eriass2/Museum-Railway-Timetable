<script setup lang="ts">
import AdminLoadState from '../components/AdminLoadState.vue';
import AdminRoutesPanel from '../components/AdminRoutesPanel.vue';
import AdminStationsPanel from '../components/AdminStationsPanel.vue';
import { AdminStatusMessage } from '../components/ui';
import { useStationsRoutesPage } from '../composables/useStationsRoutesPage';
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
  editingRoute,
  routesView,
  loading,
  error,
  load,
  stationsById,
  isFlashed,
  stationTitle,
  addStation,
  addRoute,
  editRoute,
  saveRoute,
  startCreateRoute,
  requestBackToRoutesList,
  backToRoutesList,
  saveStationMeta,
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

      <div v-if="sectionTab === 'stations' && cfg.canManage" class="mrt-admin-stations-toolbar">
        <label class="mrt-admin-stations-filter">
          <input v-model="showMissingZonesOnly" type="checkbox" />
          {{ adminStr(cfg, 'stationsFilterMissingZones') }}
        </label>
      </div>

      <AdminStationsPanel
        v-if="sectionTab === 'stations'"
        v-model:new-station="newStation"
        :stations="visibleStations"
        :price-zone-options="priceZoneOptions"
        :is-flashed="isFlashed"
        @add="addStation"
        @save="saveStationMeta"
        @remove="removeStation"
      />

      <AdminRoutesPanel
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
