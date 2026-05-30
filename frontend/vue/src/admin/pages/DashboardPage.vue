<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getDashboard } from '../api/adminRest';
import type { DashboardPayload } from '../types';
import AdminNav from '../components/AdminNav.vue';

const router = useRouter();
const loading = ref(true);
const error = ref('');
const data = ref<DashboardPayload | null>(null);

onMounted(async () => {
  try {
    data.value = await getDashboard();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ladda översikt';
  } finally {
    loading.value = false;
  }
});

function openRoute(hashRoute: string) {
  const path = hashRoute.replace(/^#/, '');
  void router.push(path);
}
</script>

<template>
  <div>
    <h1>Museum Railway Timetable</h1>
    <AdminNav />

    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>

    <template v-else-if="data">
      <div class="mrt-admin-stats widefat">
        <p>
          <strong>{{ data.stats.stations }}</strong> stationer ·
          <strong>{{ data.stats.routes }}</strong> rutter ·
          <strong>{{ data.stats.timetables }}</strong> tidtabeller ·
          <strong>{{ data.stats.services }}</strong> turer ·
          <strong>{{ data.stats.train_types }}</strong> tågtyper
        </p>
      </div>

      <div v-if="data.warnings.length" class="mrt-admin-panel">
        <h2>Varningar</h2>
        <ul class="mrt-admin-warnings">
          <li v-for="w in data.warnings" :key="w.code + w.message">
            <button type="button" class="button-link" @click="openRoute(w.route)">
              {{ w.message }}
            </button>
          </li>
        </ul>
      </div>

      <div v-if="data.next_traffic.length" class="mrt-admin-panel">
        <h2>Nästa trafik</h2>
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

      <div class="mrt-admin-panel">
        <h2>Snabbstart</h2>
        <p>
          <button type="button" class="button button-primary" @click="router.push('/timetables')">
            Hantera tidtabeller
          </button>
          <button
            type="button"
            class="button"
            @click="router.push('/stations-routes')"
          >
            Stationer &amp; rutter
          </button>
          <a v-if="data.links.front" class="button" :href="data.links.front" target="_blank" rel="noopener">
            Visa webbplats
          </a>
        </p>
      </div>
    </template>
  </div>
</template>
