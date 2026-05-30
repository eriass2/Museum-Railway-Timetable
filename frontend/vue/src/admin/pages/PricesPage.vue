<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { getPrices, savePrices } from '../api/adminRest';
import type { PricesPayload } from '../api/adminRest';
import AdminNav from '../components/AdminNav.vue';
import { adminConfig } from '../types';

const cfg = adminConfig();
const loading = ref(true);
const error = ref('');
const saved = ref('');
const data = ref<PricesPayload | null>(null);

const ticketKeys = computed(() => Object.keys(data.value?.ticket_types ?? {}));
const categoryKeys = computed(() => Object.keys(data.value?.categories ?? {}));
const zones = computed(() => data.value?.zones ?? []);

onMounted(async () => {
  if (!cfg.canManage) {
    error.value = 'Du har inte behörighet att ändra priser.';
    loading.value = false;
    return;
  }
  try {
    data.value = await getPrices();
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ladda priser';
  } finally {
    loading.value = false;
  }
});

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
  saved.value = '';
  error.value = '';
  try {
    data.value = await savePrices(data.value.matrix);
    saved.value = 'Sparat.';
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte spara';
  }
}
</script>

<template>
  <div>
    <h1>Priser</h1>
    <AdminNav />

    <p v-if="loading" class="description">Laddar...</p>
    <p v-else-if="error" class="notice notice-error">{{ error }}</p>

    <form v-else-if="data" @submit.prevent="submit">
      <p class="description">
        Priser i SEK per biljettyp, passagerarkategori och antal zoner.
      </p>
      <table class="widefat striped mrt-price-matrix-table">
        <thead>
          <tr>
            <th>Biljettyp</th>
            <th v-for="cat in categoryKeys" :key="cat" :colspan="zones.length">
              {{ data.categories[cat] }}
            </th>
          </tr>
          <tr>
            <th>Zoner</th>
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
      <p class="mrt-mt-sm">
        <button type="submit" class="button button-primary">Spara priser</button>
        <span v-if="saved" class="description mrt-ml-sm">{{ saved }}</span>
      </p>
    </form>
  </div>
</template>
