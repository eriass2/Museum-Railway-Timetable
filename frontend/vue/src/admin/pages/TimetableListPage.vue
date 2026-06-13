<script setup lang="ts">
import {
  AdminBackNav,
  AdminEmptyState,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtAsyncState,
  MrtButton,
} from '../components/ui';
import { useTimetableListPage } from '../composables/useTimetableListPage';
import { adminStr } from '../utils/adminLabels';

const {
  cfg,
  isMobile,
  viewMode,
  newTitle,
  newType,
  timetableTypes,
  loading,
  error,
  items,
  load,
  cardSummary,
  backToList,
  startCreate,
  createNew,
  openEditor,
  removeTimetable,
} = useTimetableListPage();
import AdminMobilePageShell from '../components/mobile/AdminMobilePageShell.vue';
</script>

<template>
  <AdminMobilePageShell :mobile="isMobile">
    <h1>{{ adminStr(cfg, 'timetablesTitle') }}</h1>
    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'timetablesLoading')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
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
            <span class="description">{{ adminStr(cfg, 'editorTypeHint') }}</span>
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
    </MrtAsyncState>
  </AdminMobilePageShell>
</template>

<style scoped>
.mrt-admin-timetable-create select {
  max-width: 14rem;
}
</style>
