<script setup lang="ts">
import type { DisruptionFeedItem } from '@/api/disruptionFeed';
import type { DisruptionFeedEditHint } from '@/utils/disruptionFeedDisplay';
import MrtTfAlertCard from './MrtTfAlertCard.vue';

const props = defineProps<{
  items: DisruptionFeedItem[];
  expandedAlertId: string | null;
  editForItem?: (item: DisruptionFeedItem) => DisruptionFeedEditHint | null;
}>();

const emit = defineEmits<{ toggleAlert: [itemId: string] }>();

function editHint(item: DisruptionFeedItem): DisruptionFeedEditHint | null {
  return props.editForItem?.(item) ?? null;
}
</script>

<template>
  <ul class="mrt-tf-alert-list">
    <MrtTfAlertCard
      v-for="item in items"
      :key="item.id"
      :item="item"
      :expanded="expandedAlertId === item.id"
      :edit-hint="editHint(item)"
      @toggle="emit('toggleAlert', item.id)"
    />
  </ul>
</template>
