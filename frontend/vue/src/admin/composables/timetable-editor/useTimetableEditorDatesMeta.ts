import { ref, type Ref } from 'vue';
import { useRouter } from 'vue-router';
import { deleteTimetable, updateTimetable } from '../../api/adminRest';
import { adminConfirm } from '../adminConfirm';
import type { AdminClientConfig, TimetableDetail } from '../../types';
import { adminErrorMessage, adminFmt, adminStr } from '../../utils/adminLabels';

type RunMutation = <T>(fn: () => Promise<T>, fallbackKey: string) => Promise<boolean>;

export function useTimetableEditorDatesMeta(options: {
  timetableId: () => number;
  detail: Ref<TimetableDetail | null>;
  cfg: AdminClientConfig;
  error: Ref<string>;
  runMutation: RunMutation;
  syncSnapshots: () => void;
  showSaveNotice: (message: string) => void;
}) {
  const { timetableId, detail, cfg, error, runMutation, syncSnapshots, showSaveNotice } = options;
  const router = useRouter();
  const dateInput = ref('');
  const editTitle = ref('');
  const editType = ref('');

  function addDate(): void {
    if (!detail.value || !dateInput.value) {
      return;
    }
    if (!detail.value.dates.includes(dateInput.value)) {
      detail.value.dates = [...detail.value.dates, dateInput.value].sort();
    }
    dateInput.value = '';
  }

  function removeDate(date: string): void {
    if (!detail.value) {
      return;
    }
    detail.value.dates = detail.value.dates.filter((value) => value !== date);
  }

  async function saveDates(): Promise<void> {
    if (!detail.value || !cfg.canManage) {
      return;
    }
    const ok = await runMutation(async () => {
      detail.value = await updateTimetable(timetableId(), { dates: detail.value!.dates });
    }, 'saveFailed');
    if (!ok) {
      return;
    }
    syncSnapshots();
    showSaveNotice(adminStr(cfg, 'editorSavedDates'));
  }

  async function saveMeta(): Promise<void> {
    if (!detail.value || !cfg.canManage) {
      return;
    }
    const ok = await runMutation(async () => {
      detail.value = await updateTimetable(timetableId(), {
        title: editTitle.value.trim(),
        type: editType.value,
      });
    }, 'saveFailed');
    if (!ok) {
      return;
    }
    editTitle.value = detail.value!.title;
    editType.value = detail.value!.type || '';
    syncSnapshots();
    showSaveNotice(adminStr(cfg, 'editorSavedMeta'));
  }

  async function removeTimetable(): Promise<void> {
    if (!detail.value || !cfg.canManage) {
      return;
    }
    const ok = await adminConfirm({
      title: adminStr(cfg, 'timetablesDeleteTitle'),
      message: adminFmt(cfg, 'timetablesDeleteMessage', detail.value.title),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!ok) {
      return;
    }
    try {
      await deleteTimetable(timetableId());
      await router.push('/timetables');
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'timetablesDeleteFailed');
    }
  }

  function syncMetaFromDetail(next: TimetableDetail): void {
    editTitle.value = next.title;
    editType.value = next.type || '';
  }

  return {
    dateInput,
    editTitle,
    editType,
    addDate,
    removeDate,
    saveDates,
    saveMeta,
    removeTimetable,
    syncMetaFromDetail,
  };
}
