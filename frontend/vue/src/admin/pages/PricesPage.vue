<script setup lang="ts">
import { computed, ref } from 'vue';
import { getPrices, savePrices } from '../api/adminRest';
import type { PricesPayload } from '../api/adminRest';
import AdminLoadState from '../components/AdminLoadState.vue';
import { AdminFormActions, AdminPanel, AdminStatusMessage, MrtButton } from '../components/ui';
import { useAdminResource } from '../composables/useAdminResource';
import { useAdminSaveNotice } from '../composables/useAdminSaveNotice';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { saveMsg, show: showSaved } = useAdminSaveNotice();
const data = ref<PricesPayload | null>(null);

const { loading, error, load } = useAdminResource({
  beforeLoad: () => cfg.canManage,
  deniedMessage: adminStr(cfg, 'pricesNoPermission'),
  fetch: async () => {
    const payload = await getPrices();
    data.value = payload;
    return payload;
  },
  errorMessage: (e) => {
    data.value = null;
    return adminErrorMessage(cfg, e, 'pricesLoadFailed');
  },
});

const ticketKeys = computed(() => Object.keys(data.value?.ticket_types ?? {}));
const categoryKeys = computed(() => Object.keys(data.value?.categories ?? {}));
const zones = computed(() => data.value?.zones ?? []);

function cellValue(ticket: string, category: string, zone: number): number | '' {
  const v = data.value?.matrix[ticket]?.[category]?.[zone];
  return v === null || v === undefined ? '' : v;
}

function setCell(ticket: string, category: string, zone: number, raw: string) {
  if (!data.value) return;
  if (!data.value.matrix[ticket]) data.value.matrix[ticket] = {};
  if (!data.value.matrix[ticket][category]) data.value.matrix[ticket][category] = {};
  data.value.matrix[ticket][category][zone] = raw === '' ? null : Number(raw);
}

async function submit() {
  if (!data.value) return;
  error.value = '';
  try {
    data.value = await savePrices(data.value.matrix);
    showSaved(adminStr(cfg, 'saved'));
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'saveFailed');
  }
}
</script>

<template>
  <div>
    <h1>{{ adminStr(cfg, 'pricesTitle', 'Priser') }}</h1>

    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'pricesLoading')"
      @retry="load"
    >
    <AdminPanel v-if="data">
    <form @submit.prevent="submit">
      <p class="description">
        {{ adminStr(cfg, 'pricesDescription') }}
      </p>
      <table class="widefat striped mrt-price-matrix-table">
        <thead>
          <tr>
            <th>{{ adminStr(cfg, 'pricesTicketTypeCol') }}</th>
            <th v-for="cat in categoryKeys" :key="cat" :colspan="zones.length">
              {{ data.categories[cat] }}
            </th>
          </tr>
          <tr>
            <th>{{ adminStr(cfg, 'pricesZonesCol') }}</th>
            <template v-for="cat in categoryKeys" :key="`z-${cat}`">
              <th v-for="zone in zones" :key="`${cat}-${zone}`">{{ zone }}</th>
            </template>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in ticketKeys" :key="ticket">
            <th scope="row">{{ data.ticket_types[ticket] }}</th>
            <template v-for="cat in categoryKeys" :key="`${ticket}-${cat}`">
              <td v-for="zone in zones" :key="`${ticket}-${cat}-${zone}`">
                <input
                  type="number"
                  min="0"
                  step="1"
                  class="small-text"
                  :value="cellValue(ticket, cat, zone)"
                  placeholder="—"
                  @input="setCell(ticket, cat, zone, ($event.target as HTMLInputElement).value)"
                />
              </td>
            </template>
          </tr>
        </tbody>
      </table>
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" type="submit">
          {{ adminStr(cfg, 'pricesSaveButton') }}
        </MrtButton>
        <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
      </AdminFormActions>
    </form>
    </AdminPanel>
    </AdminLoadState>
  </div>
</template>
