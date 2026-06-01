<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getSettings, saveSettings } from '../api/adminRest';
import type { SettingsPayload } from '../api/adminRest';
import AdminLoadState from '../components/AdminLoadState.vue';
import { AdminFormActions, AdminPanel, AdminStatusMessage } from '../components/ui';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const loading = ref(true);
const error = ref('');
const saved = ref('');
const form = ref<SettingsPayload>({
  enabled: true,
  note: '',
  min_transfer_minutes: 3,
  max_transfer_minutes: 120,
});

async function load() {
  if (!cfg.canManage) {
    error.value = 'Du har inte behörighet att ändra inställningar.';
    loading.value = false;
    return;
  }
  loading.value = true;
  error.value = '';
  try {
    form.value = await getSettings();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ladda inställningar';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void load();
});

async function submit() {
  saved.value = '';
  error.value = '';
  try {
    form.value = await saveSettings(form.value);
    saved.value = 'Sparat.';
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte spara';
  }
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>Inställningar</h1>

    <AdminLoadState :loading="loading" :error="error" loading-text="Laddar inställningar…" @retry="load">
    <AdminPanel>
    <form @submit.prevent="submit">
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row">Aktivera plugin</th>
            <td>
              <label>
                <input v-model="form.enabled" type="checkbox" />
                Pluginet är aktivt
              </label>
            </td>
          </tr>
          <tr>
            <th scope="row">Anteckning</th>
            <td>
              <input v-model="form.note" type="text" class="regular-text" />
            </td>
          </tr>
          <tr>
            <th scope="row">Min väntetid vid byte (min)</th>
            <td>
              <input v-model.number="form.min_transfer_minutes" type="number" min="0" max="60" />
            </td>
          </tr>
          <tr>
            <th scope="row">Max väntetid vid byte (min)</th>
            <td>
              <input v-model.number="form.max_transfer_minutes" type="number" min="0" max="480" />
            </td>
          </tr>
        </tbody>
      </table>
      <AdminFormActions>
        <button type="submit" class="button button-primary">Spara inställningar</button>
        <AdminStatusMessage v-if="saved" :message="saved" />
      </AdminFormActions>
      <p class="description">
        CSV-import/export finns under fliken Import/export i menyn.
      </p>
    </form>
    </AdminPanel>
    </AdminLoadState>
  </div>
</template>
