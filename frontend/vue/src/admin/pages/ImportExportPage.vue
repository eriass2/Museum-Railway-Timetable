<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { exportCsv, importCsv } from '../api/adminRest';
import AdminNav from '../components/AdminNav.vue';
import { adminConfig } from '../types';

const cfg = adminConfig();
const loading = ref(false);
const error = ref('');
const success = ref('');
const mode = ref<'merge' | 'override'>('merge');
const includePrices = ref(true);
const includeSettings = ref(true);

async function onExport() {
  if (!cfg.canManage) return;
  loading.value = true;
  error.value = '';
  success.value = '';
  try {
    const res = await exportCsv({
      include_prices: includePrices.value,
      include_settings: includeSettings.value,
    });
    const binary = atob(res.content_base64);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
      bytes[i] = binary.charCodeAt(i);
    }
    const blob = new Blob([bytes], { type: 'application/zip' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = res.filename;
    a.click();
    URL.revokeObjectURL(url);
    success.value = 'Export klar.';
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Export misslyckades';
  } finally {
    loading.value = false;
  }
}

async function onImport(ev: Event) {
  if (!cfg.canManage) return;
  const input = ev.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  loading.value = true;
  error.value = '';
  success.value = '';
  try {
    const res = await importCsv(file, mode.value);
    const stats = Object.entries(res.stats)
      .map(([k, v]) => `${k}: ${v}`)
      .join(', ');
    success.value = `Import klar (${res.mode}). ${stats}`;
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Import misslyckades';
  } finally {
    loading.value = false;
    input.value = '';
  }
}
</script>

<template>
  <div>
    <h1>Import / export</h1>
    <AdminNav />

    <p v-if="!cfg.canManage" class="notice notice-warning">Du har inte behörighet.</p>
    <p v-if="error" class="notice notice-error">{{ error }}</p>
    <p v-if="success" class="notice notice-success">{{ success }}</p>

    <div v-if="cfg.canManage" class="mrt-admin-panel">
      <h2>Exportera CSV (zip)</h2>
      <p>
        <label>
          <input v-model="includeSettings" type="checkbox" />
          Inkludera inställningar
        </label>
        <label class="mrt-ml-sm">
          <input v-model="includePrices" type="checkbox" />
          Inkludera priser
        </label>
      </p>
      <p>
        <button type="button" class="button button-primary" :disabled="loading" @click="onExport">
          Ladda ner export
        </button>
      </p>

      <h2>Importera CSV (zip)</h2>
      <p class="description">Se docs/CSV_FORMAT.md för kolumnformat.</p>
      <p>
        <label>
          <input v-model="mode" type="radio" value="merge" />
          Slå ihop (uppdatera befintlig data)
        </label>
        <label class="mrt-ml-sm">
          <input v-model="mode" type="radio" value="override" />
          Ersätt (ta bort poster som saknas i filen)
        </label>
      </p>
      <p>
        <input type="file" accept=".zip,application/zip" :disabled="loading" @change="onImport" />
      </p>
    </div>
  </div>
</template>
