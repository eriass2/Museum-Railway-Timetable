<script setup lang="ts">
import MrtDisruptionFeedSections from '@/components/traffic-notices/MrtDisruptionFeedSections.vue';
import MrtAlert from '@/components/ui/MrtAlert.vue';
import { useDisruptionFeedView, type DisruptionFeedViewConfig } from '@/composables/useDisruptionFeedView';

const props = defineProps<{
  config: DisruptionFeedViewConfig;
}>();

const { loading, error, payload, labels } = useDisruptionFeedView(() => props.config);
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

<style scoped>
.mrt-traffic-notices {
  margin-block: var(--mrt-space-md, 1rem);
}

.mrt-traffic-notices__title {
  margin: 0 0 var(--mrt-space-sm, 0.5rem);
  font-size: var(--mrt-font-size-lg, 1.125rem);
}

.mrt-traffic-notices__empty {
  margin: 0;
  color: var(--mrt-color-muted, #555);
}

.mrt-traffic-notices__loading {
  margin: 0;
}
</style>
