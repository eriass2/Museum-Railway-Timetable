<script setup lang="ts">
import {
  AdminBackNav,
  AdminDisclosure,
  AdminEmptyState,
  AdminFlashRow,
  AdminFormActions,
  AdminPageHeader,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  AdminTrainTypeCell,
  MrtAlert,
  MrtAsyncState,
  MrtButton,
  TrainTypeIconPicker,
} from '../components/ui';
import { useTrainTypesPage } from '../composables/useTrainTypesPage';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import AdminMobilePageShell from '../components/mobile/AdminMobilePageShell.vue';
import { adminStr } from '../utils/adminLabels';

const { isMobile } = useMobileAdmin();
const {
  cfg,
  items,
  iconKeys,
  viewMode,
  editingRow,
  newType,
  saveMsg,
  loading,
  error,
  load,
  isFlashed,
  backToList,
  startCreate,
  startEdit,
  addType,
  saveType,
  removeType,
} = useTrainTypesPage();
</script>

<template>
  <AdminMobilePageShell class="train-types-page" :mobile="isMobile">
    <AdminPageHeader :title="adminStr(cfg, 'trainTypesTitle')" />

    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'trainTypesLoading')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
      @retry="load"
    >
      <MrtAlert v-if="saveMsg" context="admin" variant="success">{{ saveMsg }}</MrtAlert>

      <AdminPanel v-if="viewMode === 'list'">
        <AdminEmptyState
          v-if="!items.length"
          :title="adminStr(cfg, 'trainTypesEmptyTitle')"
          :message="adminStr(cfg, 'trainTypesEmptyMessage')"
        />

        <ul v-else-if="isMobile" class="mrt-admin-card-list">
          <li v-for="row in items" :key="row.id" class="mrt-admin-card-list__item">
            <strong>{{ row.name }}</strong>
            <p class="description">
              <AdminTrainTypeCell :icon-key="row.icon_key" :name="row.name" />
            </p>
            <AdminRowActions v-if="cfg.canManage" stack>
              <MrtButton context="admin" variant="secondary" @click="startEdit(row)">
                {{ adminStr(cfg, 'edit') }}
              </MrtButton>
              <MrtButton context="admin" variant="link-delete" @click="removeType(row.id)">
                {{ adminStr(cfg, 'delete') }}
              </MrtButton>
            </AdminRowActions>
          </li>
        </ul>

        <AdminTableScroll v-else>
          <table class="widefat striped train-types-page__table">
            <thead>
              <tr>
                <th>{{ adminStr(cfg, 'trainTypesColName') }}</th>
                <th>{{ adminStr(cfg, 'trainTypesColIcon') }}</th>
                <th v-if="cfg.canManage"></th>
              </tr>
            </thead>
            <tbody>
              <AdminFlashRow v-for="row in items" :key="row.id" :active="isFlashed(row.id)">
                <td>{{ row.name }}</td>
                <td>
                  <AdminTrainTypeCell :icon-key="row.icon_key" :name="row.name" />
                </td>
                <td v-if="cfg.canManage">
                  <AdminRowActions>
                    <MrtButton context="admin" variant="secondary" @click="startEdit(row)">
                      {{ adminStr(cfg, 'edit') }}
                    </MrtButton>
                    <MrtButton context="admin" variant="link-delete" @click="removeType(row.id)">
                      {{ adminStr(cfg, 'delete') }}
                    </MrtButton>
                  </AdminRowActions>
                </td>
              </AdminFlashRow>
            </tbody>
          </table>
        </AdminTableScroll>

        <AdminFormActions v-if="cfg.canManage">
          <MrtButton context="admin" variant="primary" @click="startCreate">
            {{ adminStr(cfg, 'trainTypesCreateButton') }}
          </MrtButton>
        </AdminFormActions>
      </AdminPanel>

      <AdminPanel v-else-if="viewMode === 'create' && cfg.canManage">
        <AdminBackNav @back="backToList" />
        <h2 class="train-types-page__detail-title">{{ adminStr(cfg, 'trainTypesNewTitle') }}</h2>
        <div class="train-types-page__new">
          <label class="train-types-page__field">
            <span class="train-types-page__field-label">{{ adminStr(cfg, 'trainTypesNameLabel') }}</span>
            <input
              v-model="newType.name"
              type="text"
              :placeholder="adminStr(cfg, 'trainTypesNameLabel')"
              class="regular-text"
            />
          </label>
          <div class="train-types-page__field">
            <span class="train-types-page__field-label">{{ adminStr(cfg, 'trainTypesIconLabel') }}</span>
            <TrainTypeIconPicker
              v-model="newType.icon_key"
              :icon-keys="iconKeys"
              :aria-label="adminStr(cfg, 'trainTypesIconPickerAria')"
            />
          </div>
          <AdminDisclosure>
            <label class="train-types-page__field train-types-page__field--slug">
              <span class="train-types-page__field-label">{{ adminStr(cfg, 'trainTypesSlugOptional') }}</span>
              <input
                v-model="newType.slug"
                type="text"
                :placeholder="adminStr(cfg, 'trainTypesSlugPlaceholder')"
                class="regular-text"
              />
            </label>
          </AdminDisclosure>
          <AdminFormActions>
            <MrtButton context="admin" variant="primary" @click="addType">
              {{ adminStr(cfg, 'trainTypesCreateButton') }}
            </MrtButton>
            <MrtButton context="admin" variant="secondary" @click="backToList">
              {{ adminStr(cfg, 'cancel') }}
            </MrtButton>
          </AdminFormActions>
        </div>
      </AdminPanel>

      <AdminPanel v-else-if="viewMode === 'edit' && editingRow && cfg.canManage">
        <AdminBackNav @back="backToList" />
        <h2 class="train-types-page__detail-title">{{ adminStr(cfg, 'edit') }}: {{ editingRow.name }}</h2>
        <div class="train-types-page__new">
          <label class="train-types-page__field">
            <span class="train-types-page__field-label">{{ adminStr(cfg, 'trainTypesNameLabel') }}</span>
            <input v-model="editingRow.name" type="text" class="regular-text" />
          </label>
          <div class="train-types-page__field">
            <span class="train-types-page__field-label">{{ adminStr(cfg, 'trainTypesIconLabel') }}</span>
            <TrainTypeIconPicker
              v-model="editingRow.icon_key"
              :icon-keys="iconKeys"
              :aria-label="adminStr(cfg, 'trainTypesIconPickerAria')"
            />
          </div>
          <AdminDisclosure>
            <label class="train-types-page__field train-types-page__field--slug">
              <span class="train-types-page__field-label">{{ adminStr(cfg, 'trainTypesSlugLabel') }}</span>
              <input v-model="editingRow.slug" type="text" class="regular-text" />
            </label>
          </AdminDisclosure>
          <AdminFormActions>
            <MrtButton context="admin" variant="primary" @click="saveType">
              {{ adminStr(cfg, 'save') }}
            </MrtButton>
            <MrtButton context="admin" variant="secondary" @click="backToList">
              {{ adminStr(cfg, 'cancel') }}
            </MrtButton>
            <MrtButton context="admin" variant="link-delete" @click="removeType(editingRow.id)">
              {{ adminStr(cfg, 'delete') }}
            </MrtButton>
          </AdminFormActions>
        </div>
      </AdminPanel>
    </MrtAsyncState>
  </AdminMobilePageShell>
</template>

<style scoped>
.train-types-page__table td {
  vertical-align: middle;
}

.train-types-page__detail-title {
  margin: 0 0 16px;
  font-size: 14px;
}

.train-types-page__new {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.train-types-page__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.train-types-page__field-label {
  font-weight: 600;
  font-size: 13px;
}

.train-types-page__field--slug .regular-text {
  max-width: 20rem;
}
</style>
