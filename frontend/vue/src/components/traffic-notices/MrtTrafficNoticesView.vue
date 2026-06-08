<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { fetchTrafficNotices } from '@/api/trafficNotices';
import type { TrafficNoticesPayload } from '@/api/trafficNotices';
import MrtAlert from '@/components/ui/MrtAlert.vue';
import type { MrtRestConfig } from '@/config/types';
import type { TrafficNoticesLabels } from '@/types/trafficNotices';

const props = defineProps<{
  config: MrtRestConfig & {
    referenceDate?: string;
    days?: number;
    showGeneral?: boolean;
    showDeviations?: boolean;
    title?: string;
    labels?: TrafficNoticesLabels;
  };
}>();

const loading = ref(true);
const error = ref('');
const payload = ref<TrafficNoticesPayload | null>(null);

const labels = computed(() => ({
  empty: props.config.labels?.empty ?? 'Inga meddelanden',
  loading: props.config.labels?.loading ?? 'Laddar meddelanden…',
  error: props.config.labels?.error ?? 'Kunde inte ladda meddelanden.',
  deviationPrefix:
    props.config.labels?.deviationPrefix ?? '%1$s — Tåg %2$s, %3$s',
}));

const showDayHeadings = computed(
  () => (payload.value?.by_date.length ?? 0) > 1 || (props.config.days ?? 1) > 1,
);

function formatDeviationLine(
  notice: string,
  serviceNumber: string,
  routeLabel: string,
): string {
  const template = labels.value.deviationPrefix;
  return template
    .replace('%1$s', notice)
    .replace('%2$s', serviceNumber)
    .replace('%3$s', routeLabel);
}

async function load(): Promise<void> {
  loading.value = true;
  error.value = '';
  const res = await fetchTrafficNotices(props.config, {
    date: props.config.referenceDate,
    days: props.config.days ?? 1,
    showGeneral: props.config.showGeneral !== false,
    showDeviations: props.config.showDeviations !== false,
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
    <ul v-else-if="payload" class="mrt-traffic-notices__list">
      <li
        v-for="item in payload.general"
        :key="item.id"
        class="mrt-traffic-notices__item mrt-traffic-notices__item--general"
      >
        {{ item.text }}
      </li>
      <template v-for="group in payload.by_date" :key="group.date">
        <li
          v-if="showDayHeadings"
          class="mrt-traffic-notices__day-heading"
        >
          <span>{{ group.date_label }}</span>
        </li>
        <li
          v-for="deviation in group.deviations"
          :key="`${group.date}-${deviation.service_id}`"
          class="mrt-traffic-notices__item mrt-traffic-notices__item--deviation"
          :class="{ 'mrt-traffic-notices__item--cancelled': deviation.is_cancelled }"
        >
          {{
            formatDeviationLine(
              deviation.notice,
              deviation.service_number,
              deviation.route_label,
            )
          }}
        </li>
      </template>
    </ul>
  </div>
</template>
