import { computed, onMounted, ref, toValue, type MaybeRefOrGetter } from 'vue';
import { fetchDisruptionFeed, type DisruptionFeedPayload } from '@/api/disruptionFeed';
import type { MrtRestConfig } from '@/config/types';
import type { TrafficNoticesLabels } from '@/types/trafficNotices';

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
    return {
      empty: cfg.labels?.empty ?? 'Inga meddelanden',
      loading: cfg.labels?.loading ?? 'Laddar meddelanden…',
      error: cfg.labels?.error ?? 'Kunde inte ladda meddelanden.',
      sectionOngoing: cfg.labels?.sectionOngoing ?? 'Pågår nu',
      sectionUpcoming: cfg.labels?.sectionUpcoming ?? 'Kommande',
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
