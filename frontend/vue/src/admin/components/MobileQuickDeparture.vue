<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { getStopTimes, quickDeparture } from '../api/adminRest';
import type { TimetableServiceRow } from '../types';
import { adminConfig } from '../types';
import { adminStr } from '../utils/adminLabels';

const props = defineProps<{
  services: TimetableServiceRow[];
  canEdit: boolean;
}>();

const emit = defineEmits<{ saved: [message: string] }>();

const cfg = adminConfig();
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
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'mobileStopTimesLoadFailed');
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
    emit('saved', adminStr(cfg, 'mobileDepartureSaved'));
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'mobileSaveFailed');
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="mrt-admin-mobile-departure">
    <h3>{{ adminStr(cfg, 'mobileQuickDepartureTitle') }}</h3>
    <p class="description">{{ adminStr(cfg, 'mobileQuickDepartureHint') }}</p>
    <p v-if="error" class="notice notice-error">{{ error }}</p>
    <p>
      <label for="mrt-mobile-service">{{ adminStr(cfg, 'mobileTripLabel') }}</label>
      <select id="mrt-mobile-service" v-model.number="serviceId" class="widefat">
        <option :value="0">{{ adminStr(cfg, 'editorSelectTrip') }}</option>
        <option v-for="s in services" :key="s.id" :value="s.id">
          {{ s.title || s.route_name }}
        </option>
      </select>
    </p>
    <p v-if="serviceId">
      <label for="mrt-mobile-departure">
        {{ firstStopName }} — {{ adminStr(cfg, 'mobileDepartureSuffix') }}
      </label>
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
        {{ adminStr(cfg, 'mobileSaveDeparture') }}
      </button>
    </p>
  </div>
</template>
