<script setup lang="ts">
import {
  AdminDisclosure,
  AdminFormActions,
  AdminPageHeader,
  AdminPanel,
  MrtAlert,
  MrtButton,
} from '../components/ui';
import ImportExportGuidePanels from '../components/import-export/ImportExportGuidePanels.vue';
import { useImportExportPage } from '../composables/import-export/useImportExportPage';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import AdminMobilePageShell from '../components/mobile/AdminMobilePageShell.vue';
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
  <AdminMobilePageShell :mobile="isMobile">
    <AdminPageHeader :title="adminStr(cfg, 'importExportTitle')" :lead="guide.intro" />

    <p v-if="!cfg.canManage" class="notice notice-warning">
      {{ adminStr(cfg, 'importExportNoPermission') }}
    </p>

    <p v-if="error" class="notice notice-error mrt-admin-import-error">{{ error }}</p>
    <MrtAlert v-if="success" context="admin" variant="success">{{ success }}</MrtAlert>

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
          <p class="description">{{ guide.modeOverrideWarning }}</p>
        </AdminDisclosure>
        <p v-if="mode === 'override'" class="notice notice-warning mrt-admin-import-override-notice">
          {{ guide.modeOverrideWarning }}
        </p>
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
  </AdminMobilePageShell>
</template>

<style scoped>
.mrt-admin-help-steps {
  margin: 0 0 0 1.5em;
}

.mrt-admin-import-error {
  white-space: pre-line;
}

.mrt-admin-import-file {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.mrt-admin-import-export-options {
  margin: 12px 0 0;
}

.mrt-admin-import-override-notice {
  margin-top: 12px;
}
</style>
