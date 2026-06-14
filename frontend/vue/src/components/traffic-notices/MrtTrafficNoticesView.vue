<script setup lang="ts">
import { computed } from 'vue';
import MrtTfPanels from '@/components/traffic-notices/MrtTfPanels.vue';
import MrtAlert from '@/components/ui/MrtAlert.vue';
import { useDisruptionFeedView, type DisruptionFeedViewConfig } from '@/composables/useDisruptionFeedView';
import { resolveDisruptionPanels } from '@/utils/disruptionFeedPanels';

const props = defineProps<{
  config: DisruptionFeedViewConfig;
}>();

const { loading, error, payload, labels } = useDisruptionFeedView(() => props.config);

const panels = computed(() => {
  if (!payload.value) {
    return [];
  }
  return resolveDisruptionPanels(payload.value, labels.value);
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
    <MrtTfPanels v-else-if="panels.length" :panels="panels" />
  </div>
</template>

<style scoped>
.mrt-traffic-notices {
  margin-block: var(--mrt-spacing-md, 1rem);
}

.mrt-traffic-notices__title {
  margin: 0 0 0.5rem;
  font-size: var(--mrt-font-size-md, 1rem);
  font-weight: 700;
}

.mrt-traffic-notices__empty {
  margin: 0;
  color: var(--mrt-color-muted, #555);
}

.mrt-traffic-notices__loading {
  margin: 0;
}
</style>
