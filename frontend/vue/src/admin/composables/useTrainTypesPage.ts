import { ref, watch } from 'vue';
import {
  createTrainType,
  deleteTrainType,
  listTrainTypes,
  updateTrainType,
} from '../api/adminRest';
import type { TrainTypeRow } from '../types';
import { adminConfirm } from './adminConfirm';
import { proceedIfDiscardAllowed } from './adminDiscardGuard';
import { useAdminResource } from './useAdminResource';
import { useAdminRowFlash } from './useAdminRowFlash';
import { useAdminSaveNotice } from './useAdminSaveNotice';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

type TrainTypesView = 'list' | 'edit' | 'create';

type TrainTypeDraft = { name: string; slug: string; icon_key: string };

function typeDraftSnapshot(row: TrainTypeDraft): string {
  return JSON.stringify(row);
}

export function useTrainTypesPage() {
  const cfg = adminConfig();
  const items = ref<TrainTypeRow[]>([]);
  const iconKeys = ref<string[]>([]);
  const viewMode = ref<TrainTypesView>('list');
  const editingRow = ref<TrainTypeRow | null>(null);
  const newType = ref<TrainTypeDraft>({ name: '', slug: '', icon_key: 'diesel' });
  const formSnapshot = ref('');
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { flashRow, isFlashed } = useAdminRowFlash();

  const { loading, error, data, load, reload } = useAdminResource({
    fetch: () => listTrainTypes(),
    errorMessage: (e) => adminErrorMessage(cfg, e, 'loadFailed'),
  });

  watch(
    data,
    (res) => {
      if (!res) {
        return;
      }
      items.value = res.items;
      iconKeys.value = res.icon_keys;
    },
    { immediate: true },
  );

  function isFormDirty(): boolean {
    if (viewMode.value === 'list') {
      return false;
    }
    const current =
      viewMode.value === 'edit' && editingRow.value
        ? typeDraftSnapshot(editingRow.value)
        : typeDraftSnapshot(newType.value);
    return current !== formSnapshot.value;
  }

  function resetFormState(): void {
    editingRow.value = null;
    newType.value = { name: '', slug: '', icon_key: 'diesel' };
    viewMode.value = 'list';
    formSnapshot.value = '';
  }

  async function backToList(): Promise<void> {
    if (viewMode.value !== 'list' && !(await proceedIfDiscardAllowed(isFormDirty()))) {
      return;
    }
    resetFormState();
  }

  function startCreate(): void {
    if (!cfg.canManage) {
      return;
    }
    editingRow.value = null;
    newType.value = { name: '', slug: '', icon_key: 'diesel' };
    viewMode.value = 'create';
    formSnapshot.value = typeDraftSnapshot(newType.value);
  }

  function startEdit(row: TrainTypeRow): void {
    if (!cfg.canManage) {
      return;
    }
    editingRow.value = { ...row };
    viewMode.value = 'edit';
    formSnapshot.value = typeDraftSnapshot(editingRow.value);
  }

  async function addType() {
    if (!cfg.canManage || !newType.value.name.trim()) {
      return;
    }
    const created = await createTrainType({
      name: newType.value.name.trim(),
      slug: newType.value.slug.trim() || undefined,
      icon_key: newType.value.icon_key,
    });
    showSaveNotice(adminFmt(cfg, 'trainTypesCreated', created.name));
    resetFormState();
    await reload();
    flashRow(created.id);
  }

  async function saveType() {
    if (!cfg.canManage || !editingRow.value) {
      return;
    }
    const row = editingRow.value;
    await updateTrainType(row.id, {
      name: row.name,
      slug: row.slug,
      icon_key: row.icon_key,
    });
    showSaveNotice(adminFmt(cfg, 'trainTypesSaved', row.name));
    resetFormState();
    await reload();
    flashRow(row.id);
  }

  async function removeType(id: number) {
    if (!cfg.canManage) {
      return;
    }
    const row = items.value.find((item) => item.id === id);
    const ok = await adminConfirm({
      title: adminStr(cfg, 'trainTypesDeleteTitle'),
      message: row
        ? adminFmt(cfg, 'trainTypesDeleteMessage', row.name)
        : adminStr(cfg, 'trainTypesDeleteFallback'),
      confirmLabel: adminStr(cfg, 'delete'),
      danger: true,
    });
    if (!ok) {
      return;
    }
    await deleteTrainType(id);
    showSaveNotice(
      row
        ? adminFmt(cfg, 'trainTypesRemoved', row.name)
        : adminStr(cfg, 'trainTypesRemovedFallback'),
    );
    if (editingRow.value?.id === id) {
      resetFormState();
    }
    await reload();
  }

  return {
    cfg,
    items,
    iconKeys,
    viewMode,
    editingRow,
    newType,
    saveMsg,
    loading,
    error,
    load,
    isFlashed,
    backToList,
    startCreate,
    startEdit,
    addType,
    saveType,
    removeType,
  };
}
