<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { createTimetable, listTimetables } from '../api/adminRest';
import type { TimetableListItem } from '../types';
import AdminNav from '../components/AdminNav.vue';
import { adminConfig } from '../types';

const router = useRouter();
const cfg = adminConfig();
const items = ref<TimetableListItem[]>([]);
const loading = ref(true);
const error = ref('');
const newTitle = ref('');

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const res = await listTimetables();
    items.value = res.items;
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Fel vid laddning';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void load();
});

async function createNew() {
  if (!cfg.canManage || !newTitle.value.trim()) {
    return;
  }
  try {
    const tt = await createTimetable(newTitle.value.trim());
    newTitle.value = '';
    await router.push(`/timetables/${tt.id}`);
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte skapa';
  }
}
</script>

<template>
  <div>
    <h1>Tidtabeller</h1>
    <AdminNav />
    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>

    <div v-if="cfg.canManage" class="mrt-admin-panel">
      <h2>Ny tidtabell</h2>
      <p>
        <input v-model="newTitle" type="text" class="regular-text" placeholder="Namn" />
        <button type="button" class="button button-primary" @click="createNew">Skapa</button>
      </p>
    </div>

    <table v-if="!loading" class="widefat striped">
      <thead>
        <tr>
          <th>Namn</th>
          <th>Trafikdagar</th>
          <th>Turer</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in items" :key="row.id">
          <td>{{ row.title }}</td>
          <td>{{ row.dates_count }}</td>
          <td>{{ row.trips_count }}</td>
          <td>
            <button
              type="button"
              class="button button-small"
              @click="router.push(`/timetables/${row.id}`)"
            >
              Redigera
            </button>
          </td>
        </tr>
        <tr v-if="!items.length">
          <td colspan="4">Inga tidtabeller.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
