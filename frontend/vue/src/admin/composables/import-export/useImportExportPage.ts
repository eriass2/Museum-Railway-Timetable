import { computed, ref } from 'vue';
import {
  clearAllData,
  exportCsv,
  exportTemplateCsv,
  importCsv,
} from '../../api/adminRest';
import { adminConfirm } from '../adminConfirm';
import { adminErrorMessage, adminFmtN, adminStr } from '../../utils/adminLabels';
import { downloadBase64Zip } from '../../utils/downloadBase64File';
import { adminConfig } from '../../types';
import type { AdminImportExportGuide } from '../../types';

export function useImportExportPage() {
  const cfg = adminConfig();
  const guide = computed((): AdminImportExportGuide => {
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

  function clearMessages() {
    error.value = '';
    success.value = '';
  }

  async function runAction(
    action: () => Promise<void>,
    fallbackErrorKey: string,
  ) {
    if (!cfg.canManage) return;
    loading.value = true;
    clearMessages();
    try {
      await action();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, fallbackErrorKey);
    } finally {
      loading.value = false;
    }
  }

  async function onExport() {
    await runAction(async () => {
      const res = await exportCsv({
        include_prices: includePrices.value,
        include_settings: includeSettings.value,
      });
      downloadBase64Zip(res.filename, res.content_base64);
      success.value = adminStr(cfg, 'importExportExportSuccess');
    }, 'importExportExportFailed');
  }

  async function onTemplate() {
    await runAction(async () => {
      const res = await exportTemplateCsv();
      downloadBase64Zip(res.filename, res.content_base64);
      success.value = adminStr(cfg, 'importExportTemplateSuccess');
    }, 'importExportTemplateFailed');
  }

  function openImportPicker() {
    fileInput.value?.click();
  }

  async function onImport(ev: Event) {
    const input = ev.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    await runAction(async () => {
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
    }, 'importExportImportFailed');
    input.value = '';
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
    await runAction(async () => {
      await clearAllData();
      success.value = adminStr(cfg, 'importExportClearSuccess');
    }, 'genericError');
  }

  return {
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
  };
}
