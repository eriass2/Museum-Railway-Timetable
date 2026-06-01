<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getDashboard } from '../api/adminRest';
import type { DashboardPayload } from '../types';
import AdminLoadState from '../components/AdminLoadState.vue';
import { AdminActionBar, AdminPanel } from '../components/ui';
import AdminSetupChecklist from '../components/AdminSetupChecklist.vue';
import TrafficTodayPanel from '../components/TrafficTodayPanel.vue';
import { useMobileAdmin } from '../composables/useMobileAdmin';

const router = useRouter();
const { isMobile } = useMobileAdmin();
const loading = ref(true);
const error = ref('');
const data = ref<DashboardPayload | null>(null);

const statItems = [
  { key: 'stations', label: 'Stationer' },
  { key: 'routes', label: 'Rutter' },
  { key: 'timetables', label: 'Tidtabeller' },
  { key: 'services', label: 'Turer' },
  { key: 'train_types', label: 'Tågtyper' },
] as const;

async function load() {
  loading.value = true;
  error.value = '';
  try {
    data.value = await getDashboard();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ladda översikt';
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
    <h1>Museum Railway Timetable</h1>

    <AdminLoadState :loading="loading" :error="error" loading-text="Laddar översikt…" @retry="load">
    <template v-if="data">
      <p v-if="data.can_operate && !data.can_manage" class="notice notice-info">
        Begränsad behörighet: du kan ändra avvikelser och avgångstider, inte grunddata.
      </p>

      <TrafficTodayPanel
        v-if="data.traffic_today"
        :traffic="data.traffic_today"
        :can-operate="data.can_operate"
      />

      <AdminSetupChecklist v-if="data.can_manage" :stats="data.stats" />

      <div v-if="isMobile" class="mrt-admin-stat-grid" aria-label="Statistik">
        <div v-for="item in statItems" :key="item.key" class="mrt-admin-stat-card">
          <span class="mrt-admin-stat-card__value">{{ data.stats[item.key] }}</span>
          <span class="mrt-admin-stat-card__label">{{ item.label }}</span>
        </div>
      </div>
      <div v-else class="mrt-admin-stats widefat">
        <p>
          <strong>{{ data.stats.stations }}</strong> stationer ·
          <strong>{{ data.stats.routes }}</strong> rutter ·
          <strong>{{ data.stats.timetables }}</strong> tidtabeller ·
          <strong>{{ data.stats.services }}</strong> turer ·
          <strong>{{ data.stats.train_types }}</strong> tågtyper
        </p>
      </div>

      <AdminPanel v-if="data.warnings.length">
        <h2>Varningar</h2>
        <ul class="mrt-admin-warnings">
          <li v-for="w in data.warnings" :key="w.code + w.message">
            <button type="button" class="button-link" @click="openRoute(w.route)">
              {{ w.message }}
            </button>
          </li>
        </ul>
      </AdminPanel>

      <AdminPanel v-if="data.next_traffic.length">
        <h2>Nästa trafik</h2>
        <div class="mrt-admin-table-scroll">
          <table class="widefat striped">
            <thead>
              <tr>
                <th>Datum</th>
                <th>Tidtabell</th>
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
        <h2>Snabbstart</h2>
        <AdminActionBar>
          <button type="button" class="button button-primary" @click="router.push('/stations-routes')">
            Stationer &amp; rutter
          </button>
          <button type="button" class="button" @click="router.push('/timetables')">
            Hantera tidtabeller
          </button>
          <button type="button" class="button" @click="router.push('/help')">
            Hjälp &amp; FAQ
          </button>
          <a
            v-if="data.links.front"
            class="button"
            :href="data.links.front"
            target="_blank"
            rel="noopener"
          >
            Visa webbplats
          </a>
        </AdminActionBar>
      </AdminPanel>
    </template>
    </AdminLoadState>
  </div>
</template>
