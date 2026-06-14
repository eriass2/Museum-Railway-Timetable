import { computed, onMounted, ref, toValue, type MaybeRefOrGetter } from 'vue';
import { fetchDisruptionFeed, type DisruptionFeedPayload } from '@/api/disruptionFeed';
import type { MrtRestConfig } from '@/config/types';
import type { TrafficNoticesLabels } from '@/types/trafficNotices';
import { DEFAULT_DISRUPTION_FEED_ITEM_LABELS } from '@/utils/disruptionFeedDisplay';

export type DisruptionFeedViewConfig = MrtRestConfig & {
  referenceDate?: string;
  horizonDays?: number;
  title?: string;
  labels?: TrafficNoticesLabels;
};

export function useDisruptionFeedView(config: MaybeRefOrGetter<DisruptionFeedViewConfig>) {
  const loading = ref(true);
  const error = ref('');
  const payload = ref<DisruptionFeedPayload | null>(null);

  const labels = computed(() => {
    const cfg = toValue(config);
    const defaults = DEFAULT_DISRUPTION_FEED_ITEM_LABELS;
    return {
      empty: cfg.labels?.empty ?? 'Inga meddelanden',
      loading: cfg.labels?.loading ?? 'Laddar meddelanden…',
      error: cfg.labels?.error ?? 'Kunde inte ladda meddelanden.',
      sectionOngoing: cfg.labels?.sectionOngoing ?? 'Aktuellt trafikläge',
      sectionUpcoming: cfg.labels?.sectionUpcoming ?? 'Planerade avvikelser',
      item: {
        expandMore: cfg.labels?.expandMore ?? defaults.expandMore,
        expandDetails: cfg.labels?.expandDetails ?? defaults.expandDetails,
        routeOther: cfg.labels?.routeOther ?? defaults.routeOther,
      },
    };
  });

  const horizonDays = computed(() => toValue(config).horizonDays ?? 90);

  async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    const cfg = toValue(config);
    const res = await fetchDisruptionFeed(cfg, {
      date: cfg.referenceDate,
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

  return { loading, error, payload, labels, load };
}
