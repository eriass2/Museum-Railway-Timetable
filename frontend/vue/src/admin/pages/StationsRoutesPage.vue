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

      <AdminStationsPanel
        v-if="sectionTab === 'stations'"
        v-model:new-station="newStation"
        :stations="stations"
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
        @back="backToRoutesList"
        @remove="removeRoute"
      />
    </AdminLoadState>
  </div>
</template>
