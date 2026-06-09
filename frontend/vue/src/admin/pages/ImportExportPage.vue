<script setup lang="ts">
import {
  AdminDisclosure,
  AdminFormActions,
  AdminPanel,
  AdminStatusMessage,
  MrtButton,
} from '../components/ui';
import ImportExportGuidePanels from '../components/import-export/ImportExportGuidePanels.vue';
import { useImportExportPage } from '../composables/import-export/useImportExportPage';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminStr } from '../utils/adminLabels';

const { isMobile } = useMobileAdmin();
const {
  cfg,
  guide,
  loading,
  error,
  success,
  mode,
  includePrices,
  includeSettings,
  fileInput,
  onExport,
  onTemplate,
  openImportPicker,
  onImport,
  onClearAll,
} = useImportExportPage();
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
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
      <ImportExportGuidePanels :guide="guide" />
    </AdminDisclosure>
  </div>
</template>
