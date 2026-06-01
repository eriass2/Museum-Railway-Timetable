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
    error.value = e instanceof Error ? e.message : 'Fel';
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
  message.value = `Tågtypen «${created.name}» skapades.`;
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
  message.value = `«${row.name}» sparades.`;
  flashRow(row.id);
}

async function removeType(id: number) {
  if (!cfg.canManage) return;
  const row = items.value.find((item) => item.id === id);
  const ok = await adminConfirm({
    title: 'Ta bort tågtyp',
    message: row ? `«${row.name}» tas bort från listan.` : 'Tågtypen tas bort från listan.',
    confirmLabel: 'Ta bort',
    danger: true,
  });
  if (!ok) return;
  await deleteTrainType(id);
  message.value = row ? `«${row.name}» borttagen.` : 'Borttagen.';
  await load();
}
</script>

<template>
  <div class="train-types-page">
    <h1>Tågtyper</h1>

    <AdminLoadState :loading="loading" :error="error" loading-text="Laddar tågtyper…" @retry="load">
      <AdminStatusMessage :message="message" />

      <AdminPanel>
        <AdminEmptyState
          v-if="!items.length"
          title="Inga tågtyper ännu"
          message="Skapa den första tågtypen nedan. Ikonen visas i tidtabeller och bokningsflödet."
        />

        <AdminTableScroll v-else>
          <table class="widefat striped train-types-page__table">
            <thead>
              <tr>
                <th>Namn</th>
                <th>Ikon</th>
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
                      Slug
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
                    compact
                  />
                </td>
                <td v-if="cfg.canManage">
                  <AdminRowActions>
                    <button type="button" class="button" @click="saveType(row)">Spara</button>
                    <button type="button" class="button button-link-delete" @click="removeType(row.id)">
                      Ta bort
                    </button>
                  </AdminRowActions>
                </td>
              </AdminFlashRow>
            </tbody>
          </table>
        </AdminTableScroll>
      </AdminPanel>

      <AdminPanel v-if="cfg.canManage" title="Ny tågtyp">
        <div class="train-types-page__new">
          <label class="train-types-page__field">
            <span class="train-types-page__field-label">Namn</span>
            <input v-model="newType.name" type="text" placeholder="Namn" class="regular-text" />
          </label>
          <div class="train-types-page__field">
            <span class="train-types-page__field-label">Ikon</span>
            <TrainTypeIconPicker v-model="newType.icon_key" :icon-keys="iconKeys" />
          </div>
          <AdminDisclosure>
            <label class="train-types-page__field train-types-page__field--slug">
              <span class="train-types-page__field-label">Slug (valfritt)</span>
              <input v-model="newType.slug" type="text" placeholder="t.ex. ralsbuss" class="regular-text" />
            </label>
          </AdminDisclosure>
          <AdminFormActions>
            <button type="button" class="button button-primary" @click="addType">Skapa</button>
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
