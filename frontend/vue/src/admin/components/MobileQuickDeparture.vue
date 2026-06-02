<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { getStopTimes, quickDeparture } from '../api/adminRest';
import type { TimetableServiceRow } from '../types';
import { adminConfig } from '../types';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { AdminStatusMessage, MrtButton } from './ui';

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
    error.value = adminErrorMessage(cfg, e, 'mobileStopTimesLoadFailed');
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
    error.value = adminErrorMessage(cfg, e, 'mobileSaveFailed');
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="mrt-admin-mobile-departure">
    <h3>{{ adminStr(cfg, 'mobileQuickDepartureTitle') }}</h3>
    <p class="description">{{ adminStr(cfg, 'mobileQuickDepartureHint') }}</p>
    <AdminStatusMessage v-if="error" type="error" :message="error" />
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
      <MrtButton
        context="admin"
        variant="primary"
        wide
        :disabled="!serviceId || loading"
        @click="save"
      >
        {{ adminStr(cfg, 'mobileSaveDeparture') }}
      </MrtButton>
    </p>
  </div>
</template>
