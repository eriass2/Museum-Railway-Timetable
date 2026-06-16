<script setup lang="ts">
import type { DisruptionFeedPanel } from '@/api/disruptionFeed';
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import type { DisruptionFeedEditHint } from '@/utils/disruptionFeedDisplay';
import MrtTfPanel from './MrtTfPanel.vue';

defineProps<{
  panels: DisruptionFeedPanel[];
  editForItem?: (item: DisruptionFeedItem) => DisruptionFeedEditHint | null;
}>();
</script>

<template>
  <div class="mrt-tf-feed">
    <MrtTfPanel
      v-for="panel in panels"
      :key="panel.key"
      :panel="panel"
      :edit-for-item="editForItem"
    />
  </div>
</template>

<!-- Layout must be global (not scoped): rules target child SFCs (panel, category, alert). -->
<style>
@import '@mrt-assets/mrt-traffic-info-layout.css';
</style>
