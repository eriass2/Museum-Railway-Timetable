<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import type { DisruptionFeedItem, DisruptionFeedPayload } from '@/api/disruptionFeed';
import MrtDisruptionFeedSections, {
  type DisruptionFeedEditHint,
} from '@/components/traffic-notices/MrtDisruptionFeedSections.vue';
import { AdminPanel } from '../../components/ui';
import { fetchTrafficNoticesFeedPreview } from '../../api/adminRestTrafficNotices';
import { adminErrorMessage, adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import '@/styles/traffic-notices.css';

const props = defineProps<{
  refreshKey?: number;
}>();

const cfg = adminConfig();
const loading = ref(true);
const error = ref('');
const payload = ref<DisruptionFeedPayload | null>(null);

const sectionLabels = computed(() => ({
  sectionOngoing: adminStr(cfg, 'trafficNoticesFeedOngoing'),
  sectionUpcoming: adminStr(cfg, 'trafficNoticesFeedUpcoming'),
}));

function editForItem(item: DisruptionFeedItem): DisruptionFeedEditHint | null {
  if (!item.edit?.path) {
    return null;
  }
  return {
    path: item.edit.path,
    label: item.edit.label,
    query: item.edit.query,
  };
}

async function load(): Promise<void> {
  loading.value = true;
  error.value = '';
  try {
    payload.value = await fetchTrafficNoticesFeedPreview(90);
  } catch (e) {
    payload.value = null;
    error.value = adminErrorMessage(cfg, e, 'loadFailed');
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void load();
});

watch(
  () => props.refreshKey,
  () => {
    void load();
  },
);
</script>

<template>
  <AdminPanel class="mrt-traffic-notices mrt-traffic-notices--admin-preview">
    <h2>{{ adminStr(cfg, 'trafficNoticesFeedTitle') }}</h2>
    <p class="description">{{ adminStr(cfg, 'trafficNoticesFeedIntro') }}</p>
    <p v-if="loading" class="mrt-traffic-notices__loading">
      {{ adminStr(cfg, 'loading') }}
    </p>
    <p v-else-if="error" class="mrt-traffic-notices__error">{{ error }}</p>
    <p v-else-if="payload?.is_empty" class="mrt-traffic-notices__empty">
      {{ adminStr(cfg, 'trafficNoticesFeedEmpty') }}
    </p>
    <MrtDisruptionFeedSections
      v-else-if="payload"
      :ongoing="payload.ongoing"
      :upcoming="payload.upcoming"
      :labels="sectionLabels"
      :edit-for-item="editForItem"
    />
  </AdminPanel>
</template>
