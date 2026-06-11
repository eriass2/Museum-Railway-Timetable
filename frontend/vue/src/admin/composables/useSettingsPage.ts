import { ref, watch } from 'vue';
import { getSettings, saveSettings } from '../api/adminRest';
import type { SettingsPayload } from '../api/adminRest';
import { useAdminResource } from './useAdminResource';
import { useAdminSaveNotice } from './useAdminSaveNotice';
import { useAdminFormDirty } from './useAdminFormDirty';
import { useAdminUnsavedGuard } from './useAdminUnsavedGuard';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const DEFAULT_SETTINGS: SettingsPayload = {
  enabled: true,
  note: '',
  operator_name: '',
  ticket_url: '',
  hero_background_url: '',
  wizard_beta_enabled: false,
  wizard_feedback_enabled: false,
  min_transfer_minutes: 0,
  max_transfer_minutes: 120,
  max_transfers: 2,
  afternoon_return_threshold_minutes: 900,
};

export function useSettingsPage() {
  const cfg = adminConfig();
  const { saveMsg, show: showSaved } = useAdminSaveNotice();
  const form = ref<SettingsPayload>({ ...DEFAULT_SETTINGS });

  const { dirty, syncSnapshot } = useAdminFormDirty(form);
  useAdminUnsavedGuard(dirty);

  const { loading, error, data, load } = useAdminResource({
    beforeLoad: () => cfg.canManage,
    deniedMessage: adminStr(cfg, 'settingsNoPermission'),
    fetch: () => getSettings(),
    errorMessage: (e) => adminErrorMessage(cfg, e, 'settingsLoadFailed'),
  });

  watch(
    data,
    (payload) => {
      if (payload) {
        form.value = { ...payload };
        syncSnapshot();
      }
    },
    { immediate: true },
  );

  async function submit() {
    error.value = '';
    try {
      form.value = await saveSettings(form.value);
      syncSnapshot();
      showSaved(adminStr(cfg, 'saved'));
    } catch (e) {
      error.value = adminErrorMessage(cfg, e, 'saveFailed');
    }
  }

  return {
    cfg,
    form,
    dirty,
    loading,
    error,
    saveMsg,
    load,
    submit,
  };
}
