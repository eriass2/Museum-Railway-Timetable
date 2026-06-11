<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { fetchDisruptionFeed } from '@/api/disruptionFeed';
import type { DisruptionFeedItem, DisruptionFeedPayload } from '@/api/disruptionFeed';
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

function itemClasses(item: DisruptionFeedItem): Record<string, boolean> {
  return {
    'mrt-traffic-notices__feed-item--cancelled': item.kind === 'cancelled',
    'mrt-traffic-notices__feed-item--deviation': item.kind === 'deviation',
    'mrt-traffic-notices__feed-item--info': item.kind === 'info',
  };
}

function showBody(item: DisruptionFeedItem): boolean {
  const body = item.body.trim();
  if (body === '') {
    return false;
  }
  return body !== item.headline.trim();
}

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
    <div v-else-if="payload" class="mrt-traffic-notices__feed">
      <section
        v-if="payload.ongoing.length"
        class="mrt-traffic-notices__section"
        :aria-label="labels.sectionOngoing"
      >
        <h3 class="mrt-traffic-notices__section-title">
          {{ labels.sectionOngoing }}
        </h3>
        <ul class="mrt-traffic-notices__list">
          <li
            v-for="item in payload.ongoing"
            :key="item.id"
            class="mrt-traffic-notices__feed-item"
            :class="itemClasses(item)"
          >
            <p class="mrt-traffic-notices__date">{{ item.date_label }}</p>
            <p class="mrt-traffic-notices__headline">{{ item.headline }}</p>
            <p v-if="showBody(item)" class="mrt-traffic-notices__body">
              {{ item.body }}
            </p>
          </li>
        </ul>
      </section>
      <section
        v-if="payload.upcoming.length"
        class="mrt-traffic-notices__section"
        :aria-label="labels.sectionUpcoming"
      >
        <h3 class="mrt-traffic-notices__section-title">
          {{ labels.sectionUpcoming }}
        </h3>
        <ul class="mrt-traffic-notices__list">
          <li
            v-for="item in payload.upcoming"
            :key="item.id"
            class="mrt-traffic-notices__feed-item"
            :class="itemClasses(item)"
          >
            <p class="mrt-traffic-notices__date">{{ item.date_label }}</p>
            <p class="mrt-traffic-notices__headline">{{ item.headline }}</p>
            <p v-if="showBody(item)" class="mrt-traffic-notices__body">
              {{ item.body }}
            </p>
          </li>
        </ul>
      </section>
    </div>
  </div>
</template>
