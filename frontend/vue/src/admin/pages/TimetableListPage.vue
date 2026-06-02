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
  MrtButton,
} from '../components/ui';
import { adminConfirm } from '../composables/adminConfirm';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminFmt, adminFmtN, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const router = useRouter();
const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const items = ref<TimetableListItem[]>([]);
const loading = ref(true);
const error = ref('');
const newTitle = ref('');

function cardSummary(row: TimetableListItem): string {
  return adminFmtN(cfg, 'timetablesCardSummary', {
    1: row.dates_count,
    2: row.trips_count,
  });
}

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const res = await listTimetables();
    items.value = res.items;
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'timetablesLoadFailed');
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
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'timetablesCreateFailed');
  }
}

function openEditor(id: number) {
  void router.push(`/timetables/${id}`);
}

async function removeTimetable(id: number, title: string) {
  if (!cfg.canManage) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'timetablesDeleteTitle'),
    message: adminFmt(cfg, 'timetablesDeleteMessage', title),
    confirmLabel: adminStr(cfg, 'delete'),
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
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'timetablesDeleteFailed');
  }
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'timetablesTitle') }}</h1>
    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'timetablesLoading')"
      @retry="load"
    >
      <p v-if="!cfg.canManage" class="notice notice-info">
        {{ adminStr(cfg, 'timetablesLimitedRole') }}
      </p>

      <AdminPanel v-if="cfg.canManage" :title="adminStr(cfg, 'timetablesNewTitle')">
        <AdminInlineForm>
          <input
            v-model="newTitle"
            type="text"
            class="regular-text"
            :placeholder="adminStr(cfg, 'timetablesNamePlaceholder')"
          />
          <MrtButton context="admin" variant="primary" @click="createNew">
            {{ adminStr(cfg, 'timetablesCreateButton') }}
          </MrtButton>
        </AdminInlineForm>
      </AdminPanel>

      <ul v-if="!loading && isMobile" class="mrt-admin-card-list">
        <li v-for="row in items" :key="row.id" class="mrt-admin-card-list__item">
          <strong>{{ row.title }}</strong>
          <p class="description">{{ cardSummary(row) }}</p>
          <AdminRowActions stack>
            <MrtButton context="admin" variant="primary" @click="openEditor(row.id)">
              {{ adminStr(cfg, 'edit') }}
            </MrtButton>
            <MrtButton
              v-if="cfg.canManage"
              context="admin"
              variant="link-delete"
              @click="removeTimetable(row.id, row.title)"
            >
              {{ adminStr(cfg, 'delete') }}
            </MrtButton>
          </AdminRowActions>
        </li>
        <li v-if="!items.length">
          <AdminEmptyState
            :title="adminStr(cfg, 'timetablesEmptyTitle')"
            :message="adminStr(cfg, 'timetablesEmptyMessage')"
          />
        </li>
      </ul>

      <AdminPanel v-else-if="!loading">
        <AdminEmptyState
          v-if="!items.length"
          :title="adminStr(cfg, 'timetablesEmptyTitle')"
          :message="adminStr(cfg, 'timetablesEmptyMessage')"
        />
        <AdminTableScroll v-else>
          <table class="widefat striped">
            <thead>
              <tr>
                <th>{{ adminStr(cfg, 'timetablesColName') }}</th>
                <th>{{ adminStr(cfg, 'timetablesColDates') }}</th>
                <th>{{ adminStr(cfg, 'timetablesColTrips') }}</th>
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
                    <MrtButton context="admin" variant="secondary" @click="openEditor(row.id)">
                      {{ adminStr(cfg, 'edit') }}
                    </MrtButton>
                    <MrtButton
                      v-if="cfg.canManage"
                      context="admin"
                      variant="link-delete"
                      @click="removeTimetable(row.id, row.title)"
                    >
                      {{ adminStr(cfg, 'delete') }}
                    </MrtButton>
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
