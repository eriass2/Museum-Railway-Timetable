<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { getStopTimes, quickDeparture } from '../api/adminRest';
import type { TimetableServiceRow } from '../types';

const props = defineProps<{
  services: TimetableServiceRow[];
  canEdit: boolean;
}>();

const emit = defineEmits<{ saved: [message: string] }>();

const serviceId = ref(0);
const departure = ref('');
const firstStopName = ref('');
const loading = ref(false);
const error = ref('');

async function loadFirstStop() {
  if (!serviceId.value) {
    firstStopName.value = '';
    departure.value = '';
    return;
  }
  loading.value = true;
  error.value = '';
  try {
    const data = await getStopTimes(serviceId.value);
    const first = data.stations[0];
    firstStopName.value = first?.name || '—';
    departure.value = first?.departure_time || '';
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ladda stopptider';
  } finally {
    loading.value = false;
  }
}

watch(serviceId, () => {
  void loadFirstStop();
});

onMounted(() => {
  if (props.services.length === 1) {
    serviceId.value = props.services[0].id;
  }
});

async function save() {
  if (!props.canEdit || !serviceId.value) return;
  loading.value = true;
  error.value = '';
  try {
    await quickDeparture(serviceId.value, departure.value);
    emit('saved', 'Avgångstid sparad');
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte spara';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="mrt-admin-mobile-departure">
    <h3>Snabb avgångstid</h3>
    <p class="description">Ändra avgångstid vid första hållplatsen (mobil).</p>
    <p v-if="error" class="notice notice-error">{{ error }}</p>
    <p>
      <label for="mrt-mobile-service">Tur</label>
      <select id="mrt-mobile-service" v-model.number="serviceId" class="widefat">
        <option :value="0">— Välj tur —</option>
        <option v-for="s in services" :key="s.id" :value="s.id">
          {{ s.title || s.route_name }}
        </option>
      </select>
    </p>
    <p v-if="serviceId">
      <label for="mrt-mobile-departure">{{ firstStopName }} — avgång</label>
      <input
        id="mrt-mobile-departure"
        v-model="departure"
        type="time"
        class="widefat"
        :disabled="!canEdit || loading"
      />
    </p>
    <p v-if="canEdit">
      <button
        type="button"
        class="button button-primary widefat"
        :disabled="!serviceId || loading"
        @click="save"
      >
        Spara avgångstid
      </button>
    </p>
  </div>
</template>
