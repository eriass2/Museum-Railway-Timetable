import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { createTimetable, deleteTimetable, listTimetables } from '../api/adminRest';
import { adminConfirm } from './adminConfirm';
import { useAdminResource } from './useAdminResource';
import { useMobileAdmin } from './mobile/useMobileAdmin';
import { adminErrorMessage, adminFmt, adminFmtN, adminStr } from '../utils/adminLabels';
import { buildTimetableTypeOptions } from '../utils/timetableTypeOptions';
import { adminConfig } from '../types';

type TimetablesView = 'list' | 'create';

export function useTimetableListPage() {
  const router = useRouter();
  const cfg = adminConfig();
  const { isMobile } = useMobileAdmin();
  const viewMode = ref<TimetablesView>('list');
  const newTitle = ref('');
  const newType = ref('');

  const timetableTypes = computed(() => buildTimetableTypeOptions(cfg));

  const { loading, error, data, load, reload } = useAdminResource({
    fetch: () => listTimetables(),
    errorMessage: (e) => adminErrorMessage(cfg, e, 'timetablesLoadFailed'),
  });

  const items = computed(() => data.value?.items ?? []);

  function cardSummary(row: (typeof items.value)[number]): string {
    return adminFmtN(cfg, 'timetablesCardSummary', {
      1: row.dates_count,
      2: row.trips_count,
    });
  }

  function backToList(): void {
    newTitle.value = '';
    newType.value = '';
    viewMode.value = 'list';
  }

  function startCreate(): void {
    if (!cfg.canManage) {
      return;
    }
    newTitle.value = '';
    newType.value = '';
    viewMode.value = 'create';
  }

  async function createNew() {
    if (!cfg.canManage || !newTitle.value.trim()) {
      return;
    }
    try {
      const tt = await createTimetable({
        title: newTitle.value.trim(),
        type: newType.value || undefined,
      });
      backToList();
      await router.push(`/timetables/${tt.id}`);
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'timetablesCreateFailed');
    }
  }

  function openEditor(id: number) {
    void router.push(`/timetables/${id}`);
  }

  async function removeTimetable(id: number, title: string) {
    if (!cfg.canManage) {
      return;
    }
    const ok = await adminConfirm({
      title: adminStr(cfg, 'timetablesDeleteTitle'),
      message: adminFmt(cfg, 'timetablesDeleteMessage', title),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!ok) {
      return;
    }
    error.value = '';
    try {
      await deleteTimetable(id);
      await reload();
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'timetablesDeleteFailed');
    }
  }

  watch(isMobile, (mobile) => {
    if (mobile && viewMode.value === 'create') {
      backToList();
    }
  });

  return {
    cfg,
    isMobile,
    viewMode,
    newTitle,
    newType,
    timetableTypes,
    loading,
    error,
    items,
    load,
    cardSummary,
    backToList,
    startCreate,
    createNew,
    openEditor,
    removeTimetable,
  };
}
