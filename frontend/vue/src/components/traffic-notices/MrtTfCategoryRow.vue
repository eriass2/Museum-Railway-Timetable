<script setup lang="ts">
import type { DisruptionFeedCategory } from '@/api/disruptionFeed';
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import type { DisruptionFeedEditHint } from '@/utils/disruptionFeedDisplay';
import MrtTfAlertList from './MrtTfAlertList.vue';
import MrtTfCountBadge from './MrtTfCountBadge.vue';

const props = defineProps<{
  category: DisruptionFeedCategory;
  expanded: boolean;
  expandedAlertId: string | null;
  editForItem?: (item: DisruptionFeedItem) => DisruptionFeedEditHint | null;
}>();

const emit = defineEmits<{
  toggle: [];
  toggleAlert: [itemId: string];
}>();
</script>

<template>
  <div class="mrt-tf-category" :class="{ 'is-expanded': expanded }">
    <button
      type="button"
      class="mrt-tf-category__row"
      :aria-expanded="expanded"
      :aria-label="category.label"
      @click="emit('toggle')"
    >
      <span class="mrt-tf-category__label">{{ category.label }}</span>
      <span class="mrt-tf-category__badges">
        <MrtTfCountBadge
          v-if="category.counts.info > 0"
          variant="info"
          :count="category.counts.info"
        />
        <MrtTfCountBadge
          v-if="category.counts.warning > 0"
          variant="warning"
          :count="category.counts.warning"
        />
      </span>
      <span class="mrt-tf-category__expand" aria-hidden="true">
        {{ expanded ? '−' : '+' }}
      </span>
    </button>
    <div v-if="expanded" class="mrt-tf-category__alerts">
      <MrtTfAlertList
        :items="category.items"
        :expanded-alert-id="expandedAlertId"
        :edit-for-item="editForItem"
        @toggle-alert="emit('toggleAlert', $event)"
      />
    </div>
  </div>
</template>

<style scoped>
@import '@/components/ui/mrtFocusRing.css';
</style>
