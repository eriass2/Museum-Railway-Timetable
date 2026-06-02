<script setup lang="ts">
import { onMounted, ref } from 'vue';
import {
  createTrainType,
  deleteTrainType,
  listTrainTypes,
  updateTrainType,
} from '../api/adminRest';
import type { TrainTypeRow } from '../types';
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminDisclosure,
  AdminEmptyState,
  AdminFlashRow,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminStatusMessage,
  AdminTableScroll,
  TrainTypeIconPicker,
} from '../components/ui';
import { adminConfirm } from '../composables/adminConfirm';
import { useAdminRowFlash } from '../composables/useAdminRowFlash';
import { adminFmt, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const cfg = adminConfig();
const items = ref<TrainTypeRow[]>([]);
const iconKeys = ref<string[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');
const newType = ref({ name: '', slug: '', icon_key: 'diesel' });
const { flashRow, isFlashed } = useAdminRowFlash();

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const res = await listTrainTypes();
    items.value = res.items;
    iconKeys.value = res.icon_keys;
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'loadFailed');
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void load();
});

async function addType() {
  if (!cfg.canManage || !newType.value.name.trim()) return;
  const created = await createTrainType({
    name: newType.value.name.trim(),
    slug: newType.value.slug.trim() || undefined,
    icon_key: newType.value.icon_key,
  });
  newType.value = { name: '', slug: '', icon_key: 'diesel' };
  message.value = adminFmt(cfg, 'trainTypesCreated', created.name);
  await load();
  flashRow(created.id);
}

async function saveType(row: TrainTypeRow) {
  if (!cfg.canManage) return;
  await updateTrainType(row.id, {
    name: row.name,
    slug: row.slug,
    icon_key: row.icon_key,
  });
  message.value = adminFmt(cfg, 'trainTypesSaved', row.name);
  flashRow(row.id);
}

async function removeType(id: number) {
  if (!cfg.canManage) return;
  const row = items.value.find((item) => item.id === id);
  const ok = await adminConfirm({
    title: adminStr(cfg, 'trainTypesDeleteTitle'),
    message: row
      ? adminFmt(cfg, 'trainTypesDeleteMessage', row.name)
      : adminStr(cfg, 'trainTypesDeleteFallback'),
    confirmLabel: adminStr(cfg, 'delete'),
    danger: true,
  });
  if (!ok) return;
  await deleteTrainType(id);
  message.value = row
    ? adminFmt(cfg, 'trainTypesRemoved', row.name)
    : adminStr(cfg, 'trainTypesRemovedFallback');
  await load();
}
</script>

<template>
  <div class="train-types-page">
    <h1>{{ adminStr(cfg, 'trainTypesTitle') }}</h1>

    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'trainTypesLoading')"
      @retry="load"
    >
      <AdminStatusMessage :message="message" />

      <AdminPanel>
        <AdminEmptyState
          v-if="!items.length"
          :title="adminStr(cfg, 'trainTypesEmptyTitle')"
          :message="adminStr(cfg, 'trainTypesEmptyMessage')"
        />

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
                <td>
                  <input
                    v-model="row.name"
                    type="text"
                    class="regular-text"
                    :disabled="!cfg.canManage"
                  />
                  <AdminDisclosure v-if="cfg.canManage" class="train-types-page__slug">
                    <label class="train-types-page__slug-label">
                      {{ adminStr(cfg, 'trainTypesSlugLabel') }}
                      <input v-model="row.slug" type="text" class="regular-text" />
                    </label>
                  </AdminDisclosure>
                  <span v-else-if="row.slug" class="train-types-page__slug-readonly description">
                    {{ row.slug }}
                  </span>
                </td>
                <td>
                  <TrainTypeIconPicker
                    v-model="row.icon_key"
                    :icon-keys="iconKeys"
                    :disabled="!cfg.canManage"
                    :aria-label="adminStr(cfg, 'trainTypesIconPickerAria')"
                    compact
                  />
                </td>
                <td v-if="cfg.canManage">
                  <AdminRowActions>
                    <button type="button" class="button" @click="saveType(row)">
                      {{ adminStr(cfg, 'save') }}
                    </button>
                    <button type="button" class="button button-link-delete" @click="removeType(row.id)">
                      {{ adminStr(cfg, 'delete') }}
                    </button>
                  </AdminRowActions>
                </td>
              </AdminFlashRow>
            </tbody>
          </table>
        </AdminTableScroll>
      </AdminPanel>

      <AdminPanel v-if="cfg.canManage" :title="adminStr(cfg, 'trainTypesNewTitle')">
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
            <button type="button" class="button button-primary" @click="addType">
              {{ adminStr(cfg, 'trainTypesCreateButton') }}
            </button>
          </AdminFormActions>
        </div>
      </AdminPanel>
    </AdminLoadState>
  </div>
</template>

<style scoped>
.train-types-page__table td {
  vertical-align: top;
}

.train-types-page__slug {
  margin-top: 6px;
}

.train-types-page__slug-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
  color: #50575e;
}

.train-types-page__slug-readonly {
  display: block;
  margin-top: 4px;
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
