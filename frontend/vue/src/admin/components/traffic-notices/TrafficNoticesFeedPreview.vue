<script setup lang="ts">
import { computed, toRef } from 'vue';
import MrtTfPanels from '@/components/traffic-notices/MrtTfPanels.vue';
import { AdminPanel } from '../ui';
import { useTrafficNoticesFeedPreview } from '../../composables/traffic-notices/useTrafficNoticesFeedPreview';
import { adminStr } from '../../utils/adminLabels';
import { resolveDisruptionPanels } from '@/utils/disruptionFeedPanels';

const props = defineProps<{
  refreshKey?: number;
}>();

const { cfg, loading, error, payload, editForItem } = useTrafficNoticesFeedPreview(
  toRef(props, 'refreshKey'),
);

const panels = computed(() => {
  if (!payload.value) {
    return [];
  }
  return resolveDisruptionPanels(payload.value);
});
</script>

<template>
  <AdminPanel
    class="mrt-traffic-notices-admin-preview"
    :title="adminStr(cfg, 'trafficNoticesFeedTitle')"
  >
    <p class="mrt-traffic-notices-admin-preview__intro">
      {{ adminStr(cfg, 'trafficNoticesFeedIntro') }}
    </p>
    <p v-if="loading" class="mrt-traffic-notices__loading">
      {{ adminStr(cfg, 'loading') }}
    </p>
    <p v-else-if="error" class="mrt-traffic-notices__error">{{ error }}</p>
    <p v-else-if="payload?.is_empty" class="mrt-traffic-notices__empty">
      {{ adminStr(cfg, 'trafficNoticesFeedEmpty') }}
    </p>
    <div v-else-if="panels.length" class="mrt-traffic-notices-admin-preview__site">
      <p class="mrt-traffic-notices-admin-preview__caption">
        {{ adminStr(cfg, 'trafficNoticesFeedPreviewCaption') }}
      </p>
      <MrtTfPanels :panels="panels" :edit-for-item="editForItem" />
    </div>
  </AdminPanel>
</template>

<style scoped>
.mrt-traffic-notices-admin-preview__intro {
  margin: 0 0 1rem;
  color: var(--mrt-color-neutral-600, #666);
}

.mrt-traffic-notices-admin-preview__caption {
  margin: 0 0 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  color: var(--mrt-color-neutral-600, #666);
}

.mrt-traffic-notices-admin-preview__site {
  max-width: 36rem;
}
</style>
