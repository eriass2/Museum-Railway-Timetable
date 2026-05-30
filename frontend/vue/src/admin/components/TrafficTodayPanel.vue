<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { cancelTrafficToday } from '../api/adminRest';
import type { TrafficToday } from '../types';

const props = defineProps<{
  traffic: TrafficToday;
  canOperate: boolean;
}>();

const router = useRouter();
const busy = ref(false);
const message = ref('');
const error = ref('');
const localAllCancelled = ref(false);

const effectiveTraffic = computed(() => ({
  ...props.traffic,
  all_cancelled: props.traffic.all_cancelled || localAllCancelled.value,
}));

const statusText = computed(() => {
  const traffic = effectiveTraffic.value;
  if (traffic.services_count === 0) {
    return 'Inga turer schemalagda.';
  }
  if (traffic.all_cancelled) {
    return `All trafik (${traffic.services_count} turer) är inställd.`;
  }
  return `${traffic.services_count} turer · ${traffic.timetable_title}`;
});

async function cancelAll() {
  if (!props.canOperate || busy.value) return;
  if (
    !window.confirm(
      `Markera alla ${props.traffic.services_count} turer som inställda ${props.traffic.date}?`,
    )
  ) {
    return;
  }
  busy.value = true;
  message.value = '';
  error.value = '';
  try {
    const res = await cancelTrafficToday(props.traffic.date);
    message.value =
      res.services_updated > 0
        ? `${res.services_updated} turer markerade som inställda.`
        : 'Ingen trafik att ställa in.';
    if (res.services_updated > 0) {
      localAllCancelled.value = true;
    }
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Kunde inte ställa in trafik';
  } finally {
    busy.value = false;
  }
}

function openTimetable() {
  void router.push(`/timetables/${props.traffic.timetable_id}`);
}
</script>

<template>
  <div class="mrt-admin-panel mrt-admin-ops-today">
    <h2>Trafik idag</h2>
    <p class="description">{{ traffic.date }} — {{ statusText }}</p>
    <p v-if="message" class="notice notice-success">{{ message }}</p>
    <p v-if="error" class="notice notice-error">{{ error }}</p>
    <div class="mrt-admin-ops-today__actions">
      <button
        v-if="canOperate && !effectiveTraffic.all_cancelled && effectiveTraffic.services_count > 0"
        type="button"
        class="button button-primary"
        :disabled="busy"
        @click="cancelAll"
      >
        Inställ trafik idag
      </button>
      <button type="button" class="button" @click="openTimetable">
        Öppna tidtabell
      </button>
      <button
        v-if="traffic.services_count > 0"
        type="button"
        class="button"
        @click="openTimetable"
      >
        Ändra avgångstid / avvikelser
      </button>
    </div>
  </div>
</template>
