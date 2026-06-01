<script setup lang="ts">
import { onMounted, ref } from 'vue';
import {
  createTrainType,
  deleteTrainType,
  listTrainTypes,
  updateTrainType,
} from '../api/adminRest';
import type { TrainTypeRow } from '../types';
import AdminNav from '../components/AdminNav.vue';
import { adminConfirm } from '../composables/adminConfirm';
import { adminConfig } from '../types';

const cfg = adminConfig();
const items = ref<TrainTypeRow[]>([]);
const iconKeys = ref<string[]>([]);
const loading = ref(true);
const error = ref('');
const message = ref('');
const newType = ref({ name: '', slug: '', icon_key: 'diesel' });

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
  await createTrainType({
    name: newType.value.name.trim(),
    slug: newType.value.slug.trim() || undefined,
    icon_key: newType.value.icon_key,
  });
  newType.value = { name: '', slug: '', icon_key: 'diesel' };
  message.value = 'Tågtyp skapad';
  await load();
}

async function saveType(row: TrainTypeRow) {
  if (!cfg.canManage) return;
  await updateTrainType(row.id, {
    name: row.name,
    slug: row.slug,
    icon_key: row.icon_key,
  });
  message.value = 'Sparat';
}

async function removeType(id: number) {
  if (!cfg.canManage) return;
  const ok = await adminConfirm({
    title: 'Ta bort tågtyp',
    message: 'Tågtypen tas bort från listan.',
    confirmLabel: 'Ta bort',
    danger: true,
  });
  if (!ok) return;
  await deleteTrainType(id);
  message.value = 'Borttagen';
  await load();
}
</script>

<template>
  <div>
    <h1>Tågtyper</h1>
    <AdminNav />

    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>
    <p v-if="message" class="notice notice-success">{{ message }}</p>

    <table v-if="!loading" class="widefat striped">
      <thead>
        <tr>
          <th>Namn</th>
          <th>Slug</th>
          <th>Ikon</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in items" :key="row.id">
          <td>
            <input v-model="row.name" type="text" class="regular-text" :disabled="!cfg.canManage" />
          </td>
          <td>
            <input v-model="row.slug" type="text" class="regular-text" :disabled="!cfg.canManage" />
          </td>
          <td>
            <select v-model="row.icon_key" :disabled="!cfg.canManage">
              <option v-for="key in iconKeys" :key="key" :value="key">{{ key }}</option>
            </select>
          </td>
          <td>
            <button
              v-if="cfg.canManage"
              type="button"
              class="button button-small"
              @click="saveType(row)"
            >
              Spara
            </button>
            <button
              v-if="cfg.canManage"
              type="button"
              class="button button-small"
              @click="removeType(row.id)"
            >
              Ta bort
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="cfg.canManage && !loading" class="mrt-admin-panel mrt-mt-sm">
      <h2>Ny tågtyp</h2>
      <p>
        <input v-model="newType.name" type="text" placeholder="Namn" class="regular-text" />
        <input v-model="newType.slug" type="text" placeholder="Slug (valfritt)" class="regular-text" />
        <select v-model="newType.icon_key">
          <option v-for="key in iconKeys" :key="key" :value="key">{{ key }}</option>
        </select>
        <button type="button" class="button button-primary" @click="addType">Skapa</button>
      </p>
    </div>
  </div>
</template>
