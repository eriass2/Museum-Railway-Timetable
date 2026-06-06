<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { createTimetable, deleteTimetable, listTimetables } from '../api/adminRest';
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminBackNav,
  AdminEmptyState,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from '../components/ui';
import { adminConfirm } from '../composables/adminConfirm';
import { useAdminResource } from '../composables/useAdminResource';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminErrorMessage, adminFmt, adminFmtN, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

type TimetablesView = 'list' | 'create';

const router = useRouter();
const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const viewMode = ref<TimetablesView>('list');
const newTitle = ref('');
const newType = ref('');

const timetableTypes = computed(() => [
  { value: '', label: adminStr(cfg, 'editorTypeNone') },
  { value: 'green', label: adminStr(cfg, 'editorTypeGreen') },
  { value: 'yellow', label: adminStr(cfg, 'editorTypeYellow') },
  { value: 'red', label: adminStr(cfg, 'editorTypeRed') },
  { value: 'orange', label: adminStr(cfg, 'editorTypeOrange') },
] as const);

const { loading, error, data, load, reload } = useAdminResource({
  fetch: () => listTimetables(),
  errorMessage: (e) => adminErrorMessage(cfg, e, 'timetablesLoadFailed'),
});

const items = computed(() => data.value?.items ?? []);

function cardSummary(row: (typeof items.value)[number]): string {
  return adminFmtN(cfg, 'timetablesCardSummary', {
    1: row.dates_count,
    2: row.trips_count,
  });
}

function backToList(): void {
  newTitle.value = '';
  newType.value = '';
  viewMode.value = 'list';
}

function startCreate(): void {
  if (!cfg.canManage) {
    return;
  }
  newTitle.value = '';
  newType.value = '';
  viewMode.value = 'create';
}

async function createNew() {
  if (!cfg.canManage || !newTitle.value.trim()) {
    return;
  }
  try {
    const tt = await createTimetable({
      title: newTitle.value.trim(),
      type: newType.value || undefined,
    });
    backToList();
    await router.push(`/timetables/${tt.id}`);
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'timetablesCreateFailed');
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
    await reload();
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'timetablesDeleteFailed');
  }
}

watch(isMobile, (mobile) => {
  if (mobile && viewMode.value === 'create') {
    backToList();
  }
});
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

      <AdminPanel
        v-if="viewMode === 'create' && cfg.canManage && !isMobile"
        :title="adminStr(cfg, 'timetablesNewTitle')"
      >
        <AdminBackNav @back="backToList" />
        <div class="mrt-admin-timetable-create">
          <p>
            <label for="mrt-new-tt-title">{{ adminStr(cfg, 'timetablesNamePlaceholder') }}</label>
            <input
              id="mrt-new-tt-title"
              v-model="newTitle"
              type="text"
              class="regular-text"
              :placeholder="adminStr(cfg, 'timetablesNamePlaceholder')"
            />
          </p>
          <p>
            <label for="mrt-new-tt-type">{{ adminStr(cfg, 'editorTypeLabel') }}</label>
            <select id="mrt-new-tt-type" v-model="newType">
              <option v-for="opt in timetableTypes" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </p>
          <AdminFormActions>
            <MrtButton context="admin" variant="primary" @click="createNew">
              {{ adminStr(cfg, 'timetablesCreateButton') }}
            </MrtButton>
            <MrtButton context="admin" variant="secondary" @click="backToList">
              {{ adminStr(cfg, 'cancel') }}
            </MrtButton>
          </AdminFormActions>
        </div>
      </AdminPanel>

      <template v-else>
        <AdminPanel v-if="cfg.canManage && isMobile" :title="adminStr(cfg, 'timetablesNewTitle')">
          <AdminInlineForm class="mrt-admin-timetable-create">
            <input
              v-model="newTitle"
              type="text"
              class="regular-text"
              :placeholder="adminStr(cfg, 'timetablesNamePlaceholder')"
            />
            <select v-model="newType" :aria-label="adminStr(cfg, 'editorTypeLabel')">
              <option v-for="opt in timetableTypes" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
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
          <AdminFormActions v-if="cfg.canManage && !isMobile">
            <MrtButton context="admin" variant="primary" @click="startCreate">
              {{ adminStr(cfg, 'timetablesNewTitle') }}
            </MrtButton>
          </AdminFormActions>
        </AdminPanel>
      </template>
    </AdminLoadState>
  </div>
</template>
