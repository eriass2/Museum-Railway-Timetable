<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { getDashboard } from '../api/adminRest';
import { AdminActionBar, AdminPanel, MrtAsyncState, MrtButton } from '../components/ui';
import SetupChecklist from '../components/dashboard/SetupChecklist.vue';
import TrafficTodayPanel from '../components/dashboard/TrafficTodayPanel.vue';
import { useAdminResource } from '../composables/useAdminResource';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { buildDashboardStatItems } from '../utils/dashboard/dashboardStatItems';
import { adminConfig } from '../types';

const cfg = adminConfig();
const router = useRouter();
const { isMobile } = useMobileAdmin();

const { loading, error, data, load } = useAdminResource({
  fetch: () => getDashboard(),
  errorMessage: (e) => {
    return adminErrorMessage(cfg, e, 'dashboardLoadFailed');
  },
});

const statItems = computed(() => buildDashboardStatItems(cfg));

function openRoute(hashRoute: string) {
  const path = hashRoute.replace(/^#/, '');
  void router.push(path);
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'dashboardTitle', 'Museum Railway Timetable') }}</h1>

    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'dashboardLoading')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
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

      <SetupChecklist v-if="data.can_manage" :stats="data.stats" />

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
          <template v-for="(item, index) in statItems" :key="item.key">
            <strong>{{ data.stats[item.key] }}</strong> {{ item.label.toLowerCase() }}<template v-if="index < statItems.length - 1"> · </template>
          </template>
        </p>
      </div>

      <AdminPanel v-if="data.warnings.length">
        <h2>{{ adminStr(cfg, 'dashboardWarningsTitle', 'Varningar') }}</h2>
        <ul class="mrt-admin-warnings">
          <li v-for="w in data.warnings" :key="w.code + w.message">
            <MrtButton context="admin" variant="link" @click="openRoute(w.route)">
              {{ w.message }}
            </MrtButton>
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
                  <MrtButton
                    context="admin"
                    variant="link"
                    @click="router.push(`/timetables/${row.timetable_id}`)"
                  >
                    {{ row.title }}
                  </MrtButton>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </AdminPanel>

      <AdminPanel>
        <h2>{{ adminStr(cfg, 'dashboardQuickstartTitle', 'Snabbstart') }}</h2>
        <AdminActionBar>
          <MrtButton context="admin" variant="primary" @click="router.push('/stations-routes')">
            {{ adminStr(cfg, 'dashboardQuickStations', 'Stationer & rutter') }}
          </MrtButton>
          <MrtButton context="admin" variant="secondary" @click="router.push('/timetables')">
            {{ adminStr(cfg, 'dashboardQuickTimetables', 'Hantera tidtabeller') }}
          </MrtButton>
          <MrtButton context="admin" variant="secondary" @click="router.push('/help')">
            {{ adminStr(cfg, 'dashboardQuickHelp', 'Hjälp & FAQ') }}
          </MrtButton>
          <MrtButton
            v-if="data.links.front"
            context="admin"
            variant="secondary"
            :href="data.links.front"
            target="_blank"
            rel="noopener"
          >
            {{ adminStr(cfg, 'dashboardViewSite', 'Visa webbplats') }}
          </MrtButton>
        </AdminActionBar>
      </AdminPanel>
    </template>
    </MrtAsyncState>
  </div>
</template>

<style scoped>
.mrt-admin-warnings {
  list-style: disc;
  margin-left: 1.5em;
}

@media (max-width: 782px) {
  .mrt-admin-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 12px;
  }

  .mrt-admin-stat-card {
    padding: 12px;
    text-align: center;
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 3px;
  }

  .mrt-admin-stat-card__value {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.2;
  }

  .mrt-admin-stat-card__label {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #50575e;
  }
}
</style>
