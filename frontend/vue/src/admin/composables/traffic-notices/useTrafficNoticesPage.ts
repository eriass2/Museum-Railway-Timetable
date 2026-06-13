import { computed, ref, watch } from 'vue';
import {
  listTrafficNoticeMessages,
  saveTrafficNoticeMessages,
  type PublicNoticeMessage,
} from '../../api/adminRestTrafficNotices';
import { adminConfirm } from '../adminConfirm';
import { useAdminListEditor } from '../useAdminListEditor';
import { useAdminMutation } from '../useAdminMutation';
import { useAdminResource } from '../useAdminResource';
import { useAdminSaveNotice } from '../useAdminSaveNotice';
import { adminErrorMessage, adminFmtN, adminStr } from '../../utils/adminLabels';
import {
  applyDraftToMessages,
  createNoticeDraft,
  messageDraftSnapshot,
  noticeVisibilityLabelKey,
  removeMessageById,
  renumberSortOrder,
  reorderMessages,
  sortMessagesByOrder,
  TRAFFIC_NOTICE_MAX_LENGTH,
} from '../../utils/traffic-notices/trafficNoticesAdmin';
import { adminConfig } from '../../types';

export function useTrafficNoticesPage() {
  const cfg = adminConfig();
  const messages = ref<PublicNoticeMessage[]>([]);
  const feedRefreshKey = ref(0);
  const draft = ref<PublicNoticeMessage | null>(null);
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { viewMode, captureSnapshot, isDirty, guardBackToList } =
    useAdminListEditor(messageDraftSnapshot);

  const { loading, error, data, load } = useAdminResource({
    beforeLoad: () => cfg.canOperate,
    deniedMessage: adminStr(cfg, 'trafficNoticesNoPermission'),
    fetch: () => listTrafficNoticeMessages(),
    errorMessage: (e) => adminErrorMessage(cfg, e, 'loadFailed'),
  });
  const { runMutation } = useAdminMutation(error);

  watch(
    data,
    (res) => {
      if (!res) {
        return;
      }
      messages.value = sortMessagesByOrder(res.messages);
    },
    { immediate: true },
  );

  const charCountLabel = computed(() => {
    const len = (draft.value?.text ?? '').length;
    return adminFmtN(cfg, 'trafficNoticesCharCount', { 1: len, 2: TRAFFIC_NOTICE_MAX_LENGTH });
  });

  const draftVisibilityLabel = computed(() => {
    if (!draft.value) {
      return '';
    }
    return adminStr(cfg, noticeVisibilityLabelKey(draft.value));
  });

  function isFormDirty(): boolean {
    if (!draft.value) {
      return false;
    }
    return isDirty(draft.value);
  }

  function resetDraft(): void {
    draft.value = null;
    viewMode.value = 'list';
  }

  async function backToList(): Promise<void> {
    await guardBackToList(isFormDirty, resetDraft);
  }

  function startCreate(): void {
    if (!cfg.canOperate) {
      return;
    }
    draft.value = createNoticeDraft(messages.value);
    viewMode.value = 'create';
    captureSnapshot(draft.value);
  }

  function startEdit(row: PublicNoticeMessage): void {
    if (!cfg.canOperate) {
      return;
    }
    draft.value = { ...row };
    viewMode.value = 'edit';
    captureSnapshot(draft.value);
  }

  async function persistAll(next: PublicNoticeMessage[]): Promise<boolean> {
    if (!cfg.canOperate) {
      return false;
    }
    return runMutation(async () => {
      const saved = await saveTrafficNoticeMessages(renumberSortOrder(next));
      messages.value = sortMessagesByOrder(saved.messages);
      feedRefreshKey.value += 1;
      showSaveNotice(adminStr(cfg, 'trafficNoticesSaved'));
    }, 'saveFailed');
  }

  async function saveDraft(): Promise<void> {
    if (!cfg.canOperate || !draft.value || !draft.value.text.trim() || viewMode.value === 'list') {
      return;
    }
    const next = applyDraftToMessages(messages.value, draft.value, viewMode.value);
    if (await persistAll(next)) {
      resetDraft();
    }
  }

  async function removeDraft(): Promise<void> {
    if (!cfg.canOperate || !draft.value || viewMode.value !== 'edit') {
      return;
    }
    const ok = await adminConfirm({
      title: adminStr(cfg, 'trafficNoticesDelete'),
      message: adminStr(cfg, 'trafficNoticesDeleteConfirm'),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!ok) {
      return;
    }
    const next = removeMessageById(messages.value, draft.value.id);
    if (await persistAll(next)) {
      resetDraft();
    }
  }

  async function moveRow(index: number, direction: -1 | 1): Promise<void> {
    if (!cfg.canOperate) {
      return;
    }
    const target = index + direction;
    if (target < 0 || target >= messages.value.length) {
      return;
    }
    await persistAll(reorderMessages(messages.value, index, target));
  }

  return {
    cfg,
    messages,
    viewMode,
    draft,
    charCountLabel,
    draftVisibilityLabel,
    saveMsg,
    loading,
    error,
    load,
    backToList,
    startCreate,
    startEdit,
    saveDraft,
    removeDraft,
    moveRow,
    feedRefreshKey,
  };
}
