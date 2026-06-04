<script setup lang="ts">
import { ref } from 'vue';
import { clearAllData, exportCsv, importCsv } from '../api/adminRest';
import { adminConfirm } from '../composables/adminConfirm';
import { AdminFormActions, AdminPanel, AdminStatusMessage, MrtButton } from '../components/ui';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
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
    success.value = adminStr(cfg, 'importExportExportSuccess');
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'importExportExportFailed');
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
    success.value = adminFmt(cfg, 'importExportImportSuccess', res.mode, stats);
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'importExportImportFailed');
  } finally {
    loading.value = false;
    input.value = '';
  }
}

async function onClearAll() {
  if (!cfg.canManage || loading.value) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'importExportClearTitle'),
    message: adminStr(cfg, 'importExportClearMessage'),
    confirmLabel: adminStr(cfg, 'importExportClearConfirm'),
    danger: true,
  });
  if (!ok) return;
  loading.value = true;
  error.value = '';
  success.value = '';
  try {
    await clearAllData();
    success.value = adminStr(cfg, 'importExportClearSuccess');
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'genericError');
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div>
    <h1>{{ adminStr(cfg, 'importExportTitle') }}</h1>

    <p v-if="!cfg.canManage" class="notice notice-warning">
      {{ adminStr(cfg, 'importExportNoPermission') }}
    </p>
    <AdminStatusMessage v-if="error" :message="error" type="error" />
    <AdminStatusMessage :message="success" />

    <AdminPanel v-if="cfg.canManage">
      <h2>{{ adminStr(cfg, 'importExportExportTitle') }}</h2>
      <p>
        <label>
          <input v-model="includeSettings" type="checkbox" />
          {{ adminStr(cfg, 'importExportIncludeSettings') }}
        </label>
        <label class="mrt-ml-sm">
          <input v-model="includePrices" type="checkbox" />
          {{ adminStr(cfg, 'importExportIncludePrices') }}
        </label>
      </p>
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" :disabled="loading" @click="onExport">
          {{ adminStr(cfg, 'importExportDownloadButton') }}
        </MrtButton>
      </AdminFormActions>

      <h2>{{ adminStr(cfg, 'importExportImportTitle') }}</h2>
      <p class="description">{{ adminStr(cfg, 'importExportImportHint') }}</p>
      <p>
        <label>
          <input v-model="mode" type="radio" value="merge" />
          {{ adminStr(cfg, 'importExportModeMerge') }}
        </label>
        <label class="mrt-ml-sm">
          <input v-model="mode" type="radio" value="override" />
          {{ adminStr(cfg, 'importExportModeOverride') }}
        </label>
      </p>
      <p>
        <input type="file" accept=".zip,application/zip" :disabled="loading" @change="onImport" />
      </p>

      <h2>{{ adminStr(cfg, 'importExportClearTitle') }}</h2>
      <p class="description">{{ adminStr(cfg, 'importExportClearHint') }}</p>
      <AdminFormActions>
        <MrtButton
          context="admin"
          variant="link-delete"
          :disabled="loading"
          @click="onClearAll"
        >
          {{ adminStr(cfg, 'importExportClearButton') }}
        </MrtButton>
      </AdminFormActions>
    </AdminPanel>
  </div>
</template>
