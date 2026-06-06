<script setup lang="ts">
import { computed, ref } from 'vue';
import { clearAllData, exportCsv, exportTemplateCsv, importCsv } from '../api/adminRest';
import { adminConfirm } from '../composables/adminConfirm';
import {
  AdminDisclosure,
  AdminFormActions,
  AdminPanel,
  AdminStatusMessage,
  MrtButton,
} from '../components/ui';
import { adminErrorMessage, adminFmtN, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const cfg = adminConfig();
const guide = computed(() => {
  if (!cfg.importExportGuide) {
    throw new Error('mrtAdminVue.importExportGuide config missing');
  }
  return cfg.importExportGuide;
});

const loading = ref(false);
const error = ref('');
const success = ref('');
const mode = ref<'merge' | 'override'>('merge');
const includePrices = ref(true);
const includeSettings = ref(true);
const fileInput = ref<HTMLInputElement | null>(null);

function downloadBase64Zip(filename: string, contentBase64: string) {
  const binary = atob(contentBase64);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }
  const blob = new Blob([bytes], { type: 'application/zip' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

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
    downloadBase64Zip(res.filename, res.content_base64);
    success.value = adminStr(cfg, 'importExportExportSuccess');
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'importExportExportFailed');
  } finally {
    loading.value = false;
  }
}

async function onTemplate() {
  if (!cfg.canManage) return;
  loading.value = true;
  error.value = '';
  success.value = '';
  try {
    const res = await exportTemplateCsv();
    downloadBase64Zip(res.filename, res.content_base64);
    success.value = adminStr(cfg, 'importExportTemplateSuccess');
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'importExportTemplateFailed');
  } finally {
    loading.value = false;
  }
}

function openImportPicker() {
  fileInput.value?.click();
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
      .filter(([key]) => key !== 'mode')
      .map(([key, value]) => `${key}: ${value}`)
      .join(', ');
    const modeLabel =
      res.mode === 'override'
        ? adminStr(cfg, 'importExportModeOverride')
        : adminStr(cfg, 'importExportModeMerge');
    success.value = adminFmtN(cfg, 'importExportImportSuccess', {
      1: modeLabel,
      2: stats,
    });
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
  <div class="mrt-admin-page">
    <h1>{{ adminStr(cfg, 'importExportTitle') }}</h1>
    <p class="mrt-admin-page__lead">{{ guide.intro }}</p>

    <p v-if="!cfg.canManage" class="notice notice-warning">
      {{ adminStr(cfg, 'importExportNoPermission') }}
    </p>

    <p v-if="error" class="notice notice-error mrt-admin-import-error">{{ error }}</p>
    <AdminStatusMessage :message="success" />

    <AdminPanel :title="guide.workflowTitle">
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in guide.workflowSteps" :key="i">{{ step }}</li>
      </ol>
      <p class="description">{{ guide.manifestAutoNote }}</p>
    </AdminPanel>

    <template v-if="cfg.canManage">
      <AdminPanel :title="adminStr(cfg, 'importExportImportTitle')">
        <p class="description">{{ adminStr(cfg, 'importExportImportHint') }}</p>
        <p class="description">{{ adminStr(cfg, 'importExportSingleCsvHint') }}</p>
        <p class="description">{{ adminStr(cfg, 'importExportModeMergeShort') }}</p>
        <AdminFormActions>
          <MrtButton context="admin" variant="primary" :disabled="loading" @click="openImportPicker">
            {{ adminStr(cfg, 'importExportImportTitle') }}
          </MrtButton>
        </AdminFormActions>
        <input
          ref="fileInput"
          type="file"
          accept=".zip,.csv,application/zip,text/csv"
          class="mrt-admin-import-file"
          :disabled="loading"
          @change="onImport"
        />

        <AdminDisclosure :summary="adminStr(cfg, 'importExportAdvancedMode')">
          <p>
            <label>
              <input v-model="mode" type="radio" value="merge" />
              {{ adminStr(cfg, 'importExportModeMerge') }}
            </label>
          </p>
          <p>
            <label>
              <input v-model="mode" type="radio" value="override" />
              {{ adminStr(cfg, 'importExportModeOverride') }}
            </label>
          </p>
          <p class="description mrt-admin-import-warning">{{ guide.modeOverrideWarning }}</p>
        </AdminDisclosure>
      </AdminPanel>

      <AdminPanel :title="adminStr(cfg, 'importExportExportTitle')">
        <p class="description">{{ adminStr(cfg, 'importExportTemplateHint') }}</p>
        <AdminFormActions>
          <MrtButton context="admin" variant="secondary" :disabled="loading" @click="onTemplate">
            {{ adminStr(cfg, 'importExportTemplateButton') }}
          </MrtButton>
          <MrtButton context="admin" variant="primary" :disabled="loading" @click="onExport">
            {{ adminStr(cfg, 'importExportDownloadButton') }}
          </MrtButton>
        </AdminFormActions>
        <p class="mrt-admin-import-export-options">
          <label>
            <input v-model="includeSettings" type="checkbox" />
            {{ adminStr(cfg, 'importExportIncludeSettings') }}
          </label>
          <label class="mrt-ml-sm">
            <input v-model="includePrices" type="checkbox" />
            {{ adminStr(cfg, 'importExportIncludePrices') }}
          </label>
        </p>
      </AdminPanel>

      <AdminPanel :title="adminStr(cfg, 'importExportClearTitle')">
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
    </template>

    <AdminDisclosure :summary="guide.guideDisclosureSummary">
      <AdminPanel :title="guide.buildTitle">
        <ol class="mrt-admin-help-steps">
          <li v-for="(step, i) in guide.buildSteps" :key="i">{{ step }}</li>
        </ol>
      </AdminPanel>

      <AdminPanel :title="guide.packageTitle">
        <p class="description">{{ guide.packageHint }}</p>
        <table class="widefat striped mrt-admin-import-files">
          <thead>
            <tr>
              <th>{{ guide.colFile }}</th>
              <th>{{ guide.colRequired }}</th>
              <th>{{ guide.colDescription }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in guide.packageFiles" :key="row.file">
              <td><code>{{ row.file }}</code></td>
              <td>{{ row.required ? guide.requiredYes : guide.requiredNo }}</td>
              <td>{{ row.desc }}</td>
            </tr>
          </tbody>
        </table>
        <p class="description mrt-admin-import-docs">{{ guide.docsNote }}</p>
      </AdminPanel>

      <AdminPanel :title="guide.orderTitle">
        <p class="description">{{ guide.orderHint }}</p>
        <ol class="mrt-admin-help-steps">
          <li v-for="(step, i) in guide.orderSteps" :key="i"><code>{{ step }}</code></li>
        </ol>
      </AdminPanel>

      <AdminPanel :title="guide.keysTitle">
        <p>{{ guide.keysIntro }}</p>
        <ul class="mrt-admin-help-steps">
          <li v-for="(tip, i) in guide.keysTips" :key="i">{{ tip }}</li>
        </ul>
      </AdminPanel>

      <AdminPanel :title="guide.modesTitle">
        <p>{{ guide.modeMergeDetail }}</p>
        <p>{{ guide.modeOverrideDetail }}</p>
      </AdminPanel>

      <AdminPanel :title="guide.tipsTitle">
        <ul class="mrt-admin-help-steps">
          <li v-for="(tip, i) in guide.tips" :key="i">{{ tip }}</li>
        </ul>
      </AdminPanel>
    </AdminDisclosure>
  </div>
</template>
