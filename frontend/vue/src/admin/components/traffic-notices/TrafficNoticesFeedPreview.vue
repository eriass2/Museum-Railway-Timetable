<script setup lang="ts">
import { toRef } from 'vue';
import MrtDisruptionFeedSections from '@/components/traffic-notices/MrtDisruptionFeedSections.vue';
import { AdminPanel } from '../../components/ui';
import { useTrafficNoticesFeedPreview } from '../../composables/traffic-notices/useTrafficNoticesFeedPreview';
import { adminStr } from '../../utils/adminLabels';
import '@/styles/traffic-notices.css';

const props = defineProps<{
  refreshKey?: number;
}>();

const { cfg, loading, error, payload, sectionLabels, editForItem } = useTrafficNoticesFeedPreview(
  toRef(props, 'refreshKey'),
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
