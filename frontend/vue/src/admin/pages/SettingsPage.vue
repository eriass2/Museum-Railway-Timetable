<script setup lang="ts">
import { ref, watch } from 'vue';
import { getSettings, saveSettings } from '../api/adminRest';
import type { SettingsPayload } from '../api/adminRest';
import AdminLoadState from '../components/AdminLoadState.vue';
import { AdminFormActions, AdminPanel, AdminStatusMessage, MrtButton } from '../components/ui';
import { useAdminResource } from '../composables/useAdminResource';
import { useAdminSaveNotice } from '../composables/useAdminSaveNotice';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const { saveMsg, show: showSaved } = useAdminSaveNotice();
const form = ref<SettingsPayload>({
  enabled: true,
  note: '',
  min_transfer_minutes: 3,
  max_transfer_minutes: 120,
});

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
      form.value = payload;
    }
  },
  { immediate: true },
);

async function submit() {
  error.value = '';
  try {
    form.value = await saveSettings(form.value);
    showSaved(adminStr(cfg, 'saved'));
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'saveFailed');
  }
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'settingsTitle', 'Inställningar') }}</h1>

    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'settingsLoading')"
      @retry="load"
    >
    <AdminPanel>
    <form @submit.prevent="submit">
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row">{{ adminStr(cfg, 'settingsEnabledLabel') }}</th>
            <td>
              <label>
                <input v-model="form.enabled" type="checkbox" />
                {{ adminStr(cfg, 'settingsEnabledCheckbox') }}
              </label>
            </td>
          </tr>
          <tr>
            <th scope="row">{{ adminStr(cfg, 'settingsNote') }}</th>
            <td>
              <input v-model="form.note" type="text" class="regular-text" />
            </td>
          </tr>
          <tr>
            <th scope="row">{{ adminStr(cfg, 'settingsMinTransfer') }}</th>
            <td>
              <input v-model.number="form.min_transfer_minutes" type="number" min="0" max="60" />
            </td>
          </tr>
          <tr>
            <th scope="row">{{ adminStr(cfg, 'settingsMaxTransfer') }}</th>
            <td>
              <input v-model.number="form.max_transfer_minutes" type="number" min="0" max="480" />
            </td>
          </tr>
        </tbody>
      </table>
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" type="submit">
          {{ adminStr(cfg, 'settingsSaveButton') }}
        </MrtButton>
        <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
      </AdminFormActions>
      <p class="description">
        {{ adminStr(cfg, 'settingsImportHint') }}
      </p>
    </form>
    </AdminPanel>
    </AdminLoadState>
  </div>
</template>
