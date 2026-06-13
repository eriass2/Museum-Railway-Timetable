import { ref, watch } from 'vue';
import {
  createTrainType,
  deleteTrainType,
  listTrainTypes,
  updateTrainType,
} from '../api/adminRest';
import type { TrainTypeRow } from '../types';
import { adminConfirm } from './adminConfirm';
import { useAdminListEditor } from './useAdminListEditor';
import { useAdminMutation } from './useAdminMutation';
import { useAdminResource } from './useAdminResource';
import { useAdminRowFlash } from './useAdminRowFlash';
import { useAdminSaveNotice } from './useAdminSaveNotice';
import { adminErrorMessage, adminFmt, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

type TrainTypeDraft = { name: string; slug: string; icon_key: string };

function typeDraftSnapshot(row: TrainTypeDraft): string {
  return JSON.stringify(row);
}

export function useTrainTypesPage() {
  const cfg = adminConfig();
  const items = ref<TrainTypeRow[]>([]);
  const iconKeys = ref<string[]>([]);
  const editingRow = ref<TrainTypeRow | null>(null);
  const newType = ref<TrainTypeDraft>({ name: '', slug: '', icon_key: 'diesel' });
  const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();
  const { flashRow, isFlashed } = useAdminRowFlash();
  const { viewMode, captureSnapshot, isDirty, guardBackToList } =
    useAdminListEditor(typeDraftSnapshot);

  const { loading, error, data, load, reload } = useAdminResource({
    fetch: () => listTrainTypes(),
    errorMessage: (e) => adminErrorMessage(cfg, e, 'loadFailed'),
  });
  const { runMutation, runMutationWithResult } = useAdminMutation(error);

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

  function resetFormState(): void {
    editingRow.value = null;
    newType.value = { name: '', slug: '', icon_key: 'diesel' };
    viewMode.value = 'list';
  }

  function isFormDirty(): boolean {
    const current =
      viewMode.value === 'edit' && editingRow.value
        ? editingRow.value
        : newType.value;
    return isDirty(current);
  }

  async function backToList(): Promise<void> {
    await guardBackToList(isFormDirty, resetFormState);
  }

  function startCreate(): void {
    if (!cfg.canManage) {
      return;
    }
    editingRow.value = null;
    newType.value = { name: '', slug: '', icon_key: 'diesel' };
    viewMode.value = 'create';
    captureSnapshot(newType.value);
  }

  function startEdit(row: TrainTypeRow): void {
    if (!cfg.canManage) {
      return;
    }
    editingRow.value = { ...row };
    viewMode.value = 'edit';
    captureSnapshot(editingRow.value);
  }

  async function addType() {
    if (!cfg.canManage || !newType.value.name.trim()) {
      return;
    }
    const created = await runMutationWithResult(
      () =>
        createTrainType({
          name: newType.value.name.trim(),
          slug: newType.value.slug.trim() || undefined,
          icon_key: newType.value.icon_key,
        }),
      'saveFailed',
    );
    if (!created) {
      return;
    }
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
    const ok = await runMutation(() => updateTrainType(row.id, {
      name: row.name,
      slug: row.slug,
      icon_key: row.icon_key,
    }), 'saveFailed');
    if (!ok) {
      return;
    }
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
    if (!(await runMutation(() => deleteTrainType(id), 'saveFailed'))) {
      return;
    }
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
