<script setup lang="ts">
import type { FeedbackStatus } from '../api/adminRest';
import { AdminPanel, MrtAlert, MrtAsyncState, MrtButton } from '../components/ui';
import { useFeedbackPage } from '../composables/useFeedbackPage';
import { adminStr } from '../utils/adminLabels';

const {
  cfg,
  statuses,
  loading,
  exporting,
  error,
  saveMsg,
  items,
  hasItems,
  load,
  setStatus,
  onExportCsv,
  statusLabel,
} = useFeedbackPage();
</script>

<template>
  <div class="mrt-admin-page">
    <h1>{{ adminStr(cfg, 'feedbackTitle', 'Feedback') }}</h1>
    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'loading', 'Laddar…')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
      @retry="load"
    >
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
        <MrtAlert v-if="saveMsg" context="admin" variant="success">{{ saveMsg }}</MrtAlert>
      </AdminPanel>
    </MrtAsyncState>
  </div>
</template>
