<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getSettings, saveSettings } from '../api/adminRest';
import type { SettingsPayload } from '../api/adminRest';
import AdminNav from '../components/AdminNav.vue';
import { adminConfig } from '../types';

const cfg = adminConfig();
const loading = ref(true);
const error = ref('');
const saved = ref('');
const form = ref<SettingsPayload>({
  enabled: true,
  note: '',
  min_transfer_minutes: 5,
  max_transfer_minutes: 120,
});

onMounted(async () => {
  if (!cfg.canManage) {
    error.value = 'Du har inte behörighet att ändra inställningar.';
    loading.value = false;
    return;
  }
  try {
    form.value = await getSettings();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ladda inställningar';
  } finally {
    loading.value = false;
  }
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
  <div>
    <h1>Inställningar</h1>
    <AdminNav />

    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>

    <form v-else class="mrt-admin-panel" @submit.prevent="submit">
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
      <p>
        <button type="submit" class="button button-primary">Spara inställningar</button>
        <span v-if="saved" class="description mrt-ml-sm">{{ saved }}</span>
      </p>
      <p class="description">
        CSV-import/export och databasverktyg finns kvar under
        <a :href="`${cfg.adminBase.replace('page=mrt_app', 'page=mrt_settings')}`">Inställningar &amp; verktyg (legacy)</a>.
      </p>
    </form>
  </div>
</template>
