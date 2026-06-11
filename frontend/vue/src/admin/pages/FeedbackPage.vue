<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { exportFeedbackCsv, listFeedback, updateFeedbackStatus } from '../api/adminRest';
import type { FeedbackItem, FeedbackStatus } from '../api/adminRest';
import AdminLoadState from '../components/AdminLoadState.vue';
import { AdminPanel, AdminStatusMessage, MrtButton } from '../components/ui';
import { adminConfig } from '../types';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { downloadBase64Csv } from '../utils/downloadBase64File';

const cfg = adminConfig();
const loading = ref(false);
const exporting = ref(false);
const error = ref('');
const saveMsg = ref('');
const items = ref<FeedbackItem[]>([]);

const statuses: { value: FeedbackStatus; label: string }[] = [
  { value: 'new', label: 'Ny' },
  { value: 'read', label: 'Läst' },
  { value: 'resolved', label: 'Åtgärdad' },
  { value: 'dismissed', label: 'Avvisad' },
];

const hasItems = computed(() => items.value.length > 0);

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
  return statuses.find((item) => item.value === status)?.label ?? status;
}

onMounted(() => void load());
</script>

<template>
  <div class="mrt-admin-page">
    <h1>{{ adminStr(cfg, 'feedbackTitle', 'Feedback') }}</h1>
    <AdminLoadState :loading="loading" :error="error" @retry="load">
      <AdminPanel>
        <p class="description">
          {{ adminStr(cfg, 'feedbackIntro', 'Rapporter från reseplanerarens feedbackknapp.') }}
        </p>
        <p v-if="!hasItems" class="description">
          {{ adminStr(cfg, 'feedbackEmpty', 'Inga rapporter ännu.') }}
        </p>
        <table v-else class="widefat striped">
          <thead>
            <tr>
              <th>{{ adminStr(cfg, 'feedbackColType', 'Typ') }}</th>
              <th>{{ adminStr(cfg, 'feedbackColMessage', 'Beskrivning') }}</th>
              <th>{{ adminStr(cfg, 'feedbackColStep', 'Steg') }}</th>
              <th>{{ adminStr(cfg, 'feedbackColStatus', 'Status') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td>{{ item.type === 'bug' ? 'Fel' : 'Förslag' }}</td>
              <td>
                <strong>{{ item.title }}</strong>
                <p>{{ item.message }}</p>
                <p v-if="item.email" class="description">E-post: {{ item.email }}</p>
                <p v-if="item.page_url" class="description">
                  <a :href="item.page_url" target="_blank" rel="noreferrer">Öppna sida</a>
                </p>
              </td>
              <td>
                <code>{{ item.wizard_step || '—' }}</code>
              </td>
              <td>
                <label>
                  <span class="screen-reader-text">{{ statusLabel(item.status) }}</span>
                  <select
                    :value="item.status"
                    @change="setStatus(item, ($event.target as HTMLSelectElement).value as FeedbackStatus)"
                  >
                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                      {{ status.label }}
                    </option>
                  </select>
                </label>
              </td>
            </tr>
          </tbody>
        </table>
        <p>
          <MrtButton context="admin" variant="secondary" type="button" @click="load">
            {{ adminStr(cfg, 'refresh', 'Uppdatera') }}
          </MrtButton>
          <MrtButton
            context="admin"
            variant="secondary"
            type="button"
            :disabled="exporting"
            @click="onExportCsv"
          >
            {{ adminStr(cfg, 'feedbackExportButton', 'Exportera CSV') }}
          </MrtButton>
        </p>
        <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
      </AdminPanel>
    </AdminLoadState>
  </div>
</template>
