<script setup lang="ts">
import { ref } from 'vue';
import type { DisruptionFeedPanel } from '@/api/disruptionFeed';
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import type { DisruptionFeedEditHint } from '@/utils/disruptionFeedDisplay';
import MrtTfCategoryRow from './MrtTfCategoryRow.vue';
import MrtTfIconCalendar from './MrtTfIconCalendar.vue';
import MrtTfIconClock from './MrtTfIconClock.vue';

const props = defineProps<{
  panel: DisruptionFeedPanel;
  editForItem?: (item: DisruptionFeedItem) => DisruptionFeedEditHint | null;
}>();

const expandedCategoryKey = ref<string | null>(null);
const expandedAlertId = ref<string | null>(null);

function toggleCategory(key: string): void {
  expandedCategoryKey.value = expandedCategoryKey.value === key ? null : key;
  expandedAlertId.value = null;
}

function toggleAlert(itemId: string): void {
  expandedAlertId.value = expandedAlertId.value === itemId ? null : itemId;
}
</script>

<template>
  <section class="mrt-tf-panel" :aria-label="panel.title">
    <h3 class="mrt-tf-panel__header">
      <MrtTfIconClock v-if="panel.icon === 'clock'" />
      <MrtTfIconCalendar v-else />
      <span>{{ panel.title }}</span>
    </h3>
    <MrtTfCategoryRow
      v-for="category in panel.categories"
      :key="category.key"
      :category="category"
      :expanded="expandedCategoryKey === category.key"
      :expanded-alert-id="expandedAlertId"
      :edit-for-item="editForItem"
      @toggle="toggleCategory(category.key)"
      @toggle-alert="toggleAlert"
    />
  </section>
</template>
