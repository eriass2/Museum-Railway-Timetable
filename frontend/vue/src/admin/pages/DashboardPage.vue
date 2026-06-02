<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getDashboard } from '../api/adminRest';
import type { DashboardPayload } from '../types';
import AdminLoadState from '../components/AdminLoadState.vue';
import { AdminActionBar, AdminPanel } from '../components/ui';
import AdminSetupChecklist from '../components/AdminSetupChecklist.vue';
import TrafficTodayPanel from '../components/TrafficTodayPanel.vue';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const cfg = adminConfig();
const router = useRouter();
const { isMobile } = useMobileAdmin();
const loading = ref(true);
const error = ref('');
const data = ref<DashboardPayload | null>(null);

const statItems = computed(() => [
  { key: 'stations', label: adminStr(cfg, 'dashboardStatStations', 'Stationer') },
  { key: 'routes', label: adminStr(cfg, 'dashboardStatRoutes', 'Rutter') },
  { key: 'timetables', label: adminStr(cfg, 'dashboardStatTimetables', 'Tidtabeller') },
  { key: 'services', label: adminStr(cfg, 'dashboardStatServices', 'Turer') },
  { key: 'train_types', label: adminStr(cfg, 'dashboardStatTrainTypes', 'Tågtyper') },
] as const);

async function load() {
  loading.value = true;
  error.value = '';
  try {
    data.value = await getDashboard();
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'dashboardLoadFailed');
    data.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void load();
});

function openRoute(hashRoute: string) {
  const path = hashRoute.replace(/^#/, '');
  void router.push(path);
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'dashboardTitle', 'Museum Railway Timetable') }}</h1>

    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'dashboardLoading')"
      @retry="load"
    >
    <template v-if="data">
      <p v-if="data.can_operate && !data.can_manage" class="notice notice-info">
        {{ adminStr(cfg, 'dashboardLimitedRole') }}
      </p>

      <TrafficTodayPanel
        v-if="data.traffic_today"
        :traffic="data.traffic_today"
        :can-operate="data.can_operate"
      />

      <AdminSetupChecklist v-if="data.can_manage" :stats="data.stats" />

      <div
        v-if="isMobile"
        class="mrt-admin-stat-grid"
        :aria-label="adminStr(cfg, 'dashboardStatsAria', 'Statistik')"
      >
        <div v-for="item in statItems" :key="item.key" class="mrt-admin-stat-card">
          <span class="mrt-admin-stat-card__value">{{ data.stats[item.key] }}</span>
          <span class="mrt-admin-stat-card__label">{{ item.label }}</span>
        </div>
      </div>
      <div v-else class="mrt-admin-stats widefat">
        <p>
          <strong>{{ data.stats.stations }}</strong> {{ adminStr(cfg, 'dashboardStatStations').toLowerCase() }} ·
          <strong>{{ data.stats.routes }}</strong> {{ adminStr(cfg, 'dashboardStatRoutes').toLowerCase() }} ·
          <strong>{{ data.stats.timetables }}</strong> {{ adminStr(cfg, 'dashboardStatTimetables').toLowerCase() }} ·
          <strong>{{ data.stats.services }}</strong> {{ adminStr(cfg, 'dashboardStatServices').toLowerCase() }} ·
          <strong>{{ data.stats.train_types }}</strong> {{ adminStr(cfg, 'dashboardStatTrainTypes').toLowerCase() }}
        </p>
      </div>

      <AdminPanel v-if="data.warnings.length">
        <h2>{{ adminStr(cfg, 'dashboardWarningsTitle', 'Varningar') }}</h2>
        <ul class="mrt-admin-warnings">
          <li v-for="w in data.warnings" :key="w.code + w.message">
            <button type="button" class="button-link" @click="openRoute(w.route)">
              {{ w.message }}
            </button>
          </li>
        </ul>
      </AdminPanel>

      <AdminPanel v-if="data.next_traffic.length">
        <h2>{{ adminStr(cfg, 'dashboardNextTrafficTitle', 'Nästa trafik') }}</h2>
        <div class="mrt-admin-table-scroll">
          <table class="widefat striped">
            <thead>
              <tr>
                <th>{{ adminStr(cfg, 'dashboardColDate', 'Datum') }}</th>
                <th>{{ adminStr(cfg, 'dashboardColTimetable', 'Tidtabell') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in data.next_traffic" :key="row.date + row.timetable_id">
                <td>{{ row.date }}</td>
                <td>
                  <button
                    type="button"
                    class="button-link"
                    @click="router.push(`/timetables/${row.timetable_id}`)"
                  >
                    {{ row.title }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </AdminPanel>

      <AdminPanel>
        <h2>{{ adminStr(cfg, 'dashboardQuickstartTitle', 'Snabbstart') }}</h2>
        <AdminActionBar>
          <button type="button" class="button button-primary" @click="router.push('/stations-routes')">
            {{ adminStr(cfg, 'dashboardQuickStations', 'Stationer & rutter') }}
          </button>
          <button type="button" class="button" @click="router.push('/timetables')">
            {{ adminStr(cfg, 'dashboardQuickTimetables', 'Hantera tidtabeller') }}
          </button>
          <button type="button" class="button" @click="router.push('/help')">
            {{ adminStr(cfg, 'dashboardQuickHelp', 'Hjälp & FAQ') }}
          </button>
          <a
            v-if="data.links.front"
            class="button"
            :href="data.links.front"
            target="_blank"
            rel="noopener"
          >
            {{ adminStr(cfg, 'dashboardViewSite', 'Visa webbplats') }}
          </a>
        </AdminActionBar>
      </AdminPanel>
    </template>
    </AdminLoadState>
  </div>
</template>
