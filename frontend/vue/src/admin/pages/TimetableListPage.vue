<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { createTimetable, deleteTimetable, listTimetables } from '../api/adminRest';
import type { TimetableListItem } from '../types';
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminEmptyState,
  AdminInlineForm,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
} from '../components/ui';
import { adminConfirm } from '../composables/adminConfirm';
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

async function removeTimetable(id: number, title: string) {
  if (!cfg.canManage) return;
  const ok = await adminConfirm({
    title: 'Ta bort tidtabell',
    message: `«${title}» och alla dess turer raderas permanent.`,
    confirmLabel: 'Ta bort',
    danger: true,
  });
  if (!ok) {
    return;
  }
  error.value = '';
  try {
    await deleteTimetable(id);
    await load();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ta bort';
  }
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>Tidtabeller</h1>
    <AdminLoadState :loading="loading" :error="error" loading-text="Laddar tidtabeller…" @retry="load">
      <p v-if="!cfg.canManage" class="notice notice-info">
        Du kan öppna tidtabeller och ändra avvikelser eller avgångstider, men inte skapa nya
        tidtabeller eller grunddata. Kontakta en administratör om du behöver fler rättigheter.
      </p>

      <AdminPanel v-if="cfg.canManage" title="Ny tidtabell">
        <AdminInlineForm>
          <input v-model="newTitle" type="text" class="regular-text" placeholder="Namn" />
          <button type="button" class="button button-primary" @click="createNew">Skapa</button>
        </AdminInlineForm>
      </AdminPanel>

      <ul v-if="!loading && isMobile" class="mrt-admin-card-list">
        <li v-for="row in items" :key="row.id" class="mrt-admin-card-list__item">
          <strong>{{ row.title }}</strong>
          <p class="description">
            {{ row.dates_count }} trafikdagar · {{ row.trips_count }} turer
          </p>
          <AdminRowActions stack>
            <button type="button" class="button button-primary" @click="openEditor(row.id)">
              Redigera
            </button>
            <button
              v-if="cfg.canManage"
              type="button"
              class="button button-link-delete"
              @click="removeTimetable(row.id, row.title)"
            >
              Ta bort
            </button>
          </AdminRowActions>
        </li>
        <li v-if="!items.length">
          <AdminEmptyState
            title="Inga tidtabeller"
            message="Skapa en tidtabell ovan för att komma igång."
          />
        </li>
      </ul>

      <AdminPanel v-else-if="!loading">
        <AdminEmptyState
          v-if="!items.length"
          title="Inga tidtabeller"
          message="Skapa en tidtabell ovan för att komma igång."
        />
        <AdminTableScroll v-else>
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
                  <AdminRowActions>
                    <button type="button" class="button" @click="openEditor(row.id)">
                      Redigera
                    </button>
                    <button
                      v-if="cfg.canManage"
                      type="button"
                      class="button button-link-delete"
                      @click="removeTimetable(row.id, row.title)"
                    >
                      Ta bort
                    </button>
                  </AdminRowActions>
                </td>
              </tr>
            </tbody>
          </table>
        </AdminTableScroll>
      </AdminPanel>
    </AdminLoadState>
  </div>
</template>
