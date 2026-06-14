<script setup lang="ts">
import { toRef } from 'vue';
import MrtDisruptionFeedSections from '@/components/traffic-notices/MrtDisruptionFeedSections.vue';
import { useTrafficNoticesFeedPreview } from '../../composables/traffic-notices/useTrafficNoticesFeedPreview';
import { adminStr } from '../../utils/adminLabels';

const props = defineProps<{
  refreshKey?: number;
}>();

const { cfg, loading, error, payload, sectionLabels, editForItem } = useTrafficNoticesFeedPreview(
  toRef(props, 'refreshKey'),
);
</script>

<template>
  <section class="mrt-traffic-notices-admin-preview">
    <h2 class="mrt-traffic-notices-admin-preview__title">
      {{ adminStr(cfg, 'trafficNoticesFeedTitle') }}
    </h2>
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
    <div v-else-if="payload" class="mrt-traffic-notices-admin-preview__site">
      <p class="mrt-traffic-notices-admin-preview__caption">
        {{ adminStr(cfg, 'trafficNoticesFeedPreviewCaption') }}
      </p>
      <MrtDisruptionFeedSections
        :ongoing="payload.ongoing"
        :upcoming="payload.upcoming"
        :labels="sectionLabels"
        :edit-for-item="editForItem"
      />
    </div>
  </section>
</template>

<style scoped>
.mrt-traffic-notices-admin-preview {
  margin-top: var(--mrt-admin-panel-margin-top, 16px);
}

.mrt-traffic-notices-admin-preview__title {
  margin: 0 0 0.5rem;
  font-size: 1.05em;
  line-height: 1.3;
}

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
