<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { createTimetable, listTimetables } from '../api/adminRest';
import type { TimetableListItem } from '../types';
import AdminNav from '../components/AdminNav.vue';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminConfig } from '../types';

const router = useRouter();
const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
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

function openEditor(id: number) {
  void router.push(`/timetables/${id}`);
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>Tidtabeller</h1>
    <AdminNav />
    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>

    <div v-if="cfg.canManage" class="mrt-admin-panel mrt-admin-create-form">
      <h2>Ny tidtabell</h2>
      <p>
        <input v-model="newTitle" type="text" class="regular-text" placeholder="Namn" />
        <button type="button" class="button button-primary" @click="createNew">Skapa</button>
      </p>
    </div>

    <ul v-if="!loading && isMobile" class="mrt-admin-card-list">
      <li v-for="row in items" :key="row.id" class="mrt-admin-card-list__item">
        <strong>{{ row.title }}</strong>
        <p class="description">
          {{ row.dates_count }} trafikdagar · {{ row.trips_count }} turer
        </p>
        <button type="button" class="button button-primary" @click="openEditor(row.id)">
          Redigera
        </button>
      </li>
      <li v-if="!items.length" class="mrt-admin-card-list__empty">Inga tidtabeller.</li>
    </ul>

    <div v-else-if="!loading" class="mrt-admin-table-scroll">
      <table class="widefat striped">
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
              <button type="button" class="button button-small" @click="openEditor(row.id)">
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
  </div>
</template>
