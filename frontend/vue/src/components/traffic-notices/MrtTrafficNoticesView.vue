<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { fetchDisruptionFeed } from '@/api/disruptionFeed';
import type { DisruptionFeedPayload } from '@/api/disruptionFeed';
import MrtDisruptionFeedSections from '@/components/traffic-notices/MrtDisruptionFeedSections.vue';
import MrtAlert from '@/components/ui/MrtAlert.vue';
import type { MrtRestConfig } from '@/config/types';
import type { TrafficNoticesLabels } from '@/types/trafficNotices';

const props = defineProps<{
  config: MrtRestConfig & {
    referenceDate?: string;
    horizonDays?: number;
    title?: string;
    labels?: TrafficNoticesLabels;
  };
}>();

const loading = ref(true);
const error = ref('');
const payload = ref<DisruptionFeedPayload | null>(null);

const labels = computed(() => ({
  empty: props.config.labels?.empty ?? 'Inga meddelanden',
  loading: props.config.labels?.loading ?? 'Laddar meddelanden…',
  error: props.config.labels?.error ?? 'Kunde inte ladda meddelanden.',
  sectionOngoing: props.config.labels?.sectionOngoing ?? 'Pågår nu',
  sectionUpcoming: props.config.labels?.sectionUpcoming ?? 'Kommande',
}));

const horizonDays = computed(() => props.config.horizonDays ?? 90);

async function load(): Promise<void> {
  loading.value = true;
  error.value = '';
  const res = await fetchDisruptionFeed(props.config, {
    date: props.config.referenceDate,
    horizonDays: horizonDays.value,
  });
  loading.value = false;
  if (!res.success || !res.data) {
    error.value = res.message ?? labels.value.error;
    payload.value = null;
    return;
  }
  payload.value = res.data;
}

onMounted(() => {
  void load();
});
</script>

<template>
  <div class="mrt-traffic-notices">
    <h2 v-if="config.title" class="mrt-traffic-notices__title">
      {{ config.title }}
    </h2>
    <p v-if="loading" class="mrt-traffic-notices__loading">
      {{ labels.loading }}
    </p>
    <MrtAlert v-else-if="error" variant="error">
      {{ error }}
    </MrtAlert>
    <p v-else-if="payload?.is_empty" class="mrt-traffic-notices__empty">
      {{ labels.empty }}
    </p>
    <MrtDisruptionFeedSections
      v-else-if="payload"
      :ongoing="payload.ongoing"
      :upcoming="payload.upcoming"
      :labels="labels"
    />
  </div>
</template>
