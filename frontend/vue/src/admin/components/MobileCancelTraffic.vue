<script setup lang="ts">
import { ref } from 'vue';
import { cancelTrafficToday } from '../api/adminRest';
import type { TrafficToday } from '../types';

const props = defineProps<{
  traffic: TrafficToday;
  canOperate: boolean;
}>();

const emit = defineEmits<{ done: [message: string]; error: [message: string] }>();

const busy = ref(false);

async function cancelAll() {
  if (!props.canOperate || busy.value) return;
  const label = props.traffic.date;
  if (
    !window.confirm(
      `Markera alla ${props.traffic.services_count} turer som inställda ${label}?`,
    )
  ) {
    return;
  }
  busy.value = true;
  try {
    const res = await cancelTrafficToday(props.traffic.date);
    emit(
      'done',
      res.services_updated > 0
        ? `${res.services_updated} turer markerade som inställda.`
        : 'Ingen trafik att ställa in för detta datum.',
    );
  } catch (e) {
    emit('error', e instanceof Error ? e.message : 'Kunde inte ställa in trafik');
  } finally {
    busy.value = false;
  }
}
</script>

<template>
  <div class="mrt-admin-mobile-cancel">
    <h3>Inställ trafik</h3>
    <p class="description">
      Sätter meddelandet «Inställd» på alla turer som gäller {{ traffic.date }}.
    </p>
    <p v-if="traffic.all_cancelled" class="notice notice-info">
      All trafik är redan markerad som inställd.
    </p>
    <p v-else-if="canOperate">
      <button
        type="button"
        class="button button-primary widefat"
        :disabled="busy || traffic.services_count === 0"
        @click="cancelAll"
      >
        Inställ trafik idag
      </button>
    </p>
    <p v-else class="description">Du har inte behörighet att ställa in trafik.</p>
  </div>
</template>
