import { computed, onMounted, ref } from 'vue';
import { exportFeedbackCsv, listFeedback, updateFeedbackStatus } from '../api/adminRest';
import type { FeedbackItem, FeedbackStatus } from '../api/adminRest';
import { adminConfig } from '../types';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { downloadBase64Csv } from '../utils/downloadBase64File';

export function useFeedbackPage() {
  const cfg = adminConfig();
  const loading = ref(false);
  const exporting = ref(false);
  const error = ref('');
  const saveMsg = ref('');
  const items = ref<FeedbackItem[]>([]);

  const hasItems = computed(() => items.value.length > 0);

  const statuses = computed(() => [
    { value: 'new' as const, label: adminStr(cfg, 'feedbackStatusNew', 'Ny') },
    { value: 'read' as const, label: adminStr(cfg, 'feedbackStatusRead', 'Läst') },
    { value: 'resolved' as const, label: adminStr(cfg, 'feedbackStatusResolved', 'Åtgärdad') },
    { value: 'dismissed' as const, label: adminStr(cfg, 'feedbackStatusDismissed', 'Avvisad') },
  ]);

  async function load() {
    loading.value = true;
    error.value = '';
    try {
      items.value = (await listFeedback()).items;
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'feedbackLoadFailed');
    } finally {
      loading.value = false;
    }
  }

  async function setStatus(item: FeedbackItem, status: FeedbackStatus) {
    error.value = '';
    try {
      const updated = await updateFeedbackStatus(item.id, status);
      items.value = items.value.map((row) => (row.id === updated.id ? updated : row));
      saveMsg.value = adminStr(cfg, 'saved', 'Sparat.');
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'saveFailed');
    }
  }

  async function onExportCsv() {
    exporting.value = true;
    error.value = '';
    saveMsg.value = '';
    try {
      const res = await exportFeedbackCsv();
      downloadBase64Csv(res.filename, res.content_base64);
      saveMsg.value = adminStr(cfg, 'feedbackExportSuccess', 'CSV exporterad.');
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'feedbackExportFailed');
    } finally {
      exporting.value = false;
    }
  }

  function statusLabel(status: FeedbackStatus): string {
    return statuses.value.find((item) => item.value === status)?.label ?? status;
  }

  function typeLabel(type: string): string {
    if (type === 'bug') {
      return adminStr(cfg, 'feedbackTypeBug', 'Fel');
    }
    return adminStr(cfg, 'feedbackTypeSuggestion', 'Förslag');
  }

  onMounted(() => void load());

  return {
    cfg,
    statuses,
    loading,
    exporting,
    error,
    saveMsg,
    items,
    hasItems,
    load,
    setStatus,
    onExportCsv,
    statusLabel,
    typeLabel,
  };
}
