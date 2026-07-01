<script setup lang="ts">
import MrtExpandTrigger from '../MrtExpandTrigger.vue';
import MrtTimelineNode from './MrtTimelineNode.vue';

defineProps<{
  expanded: boolean;
  label: string;
}>();

const emit = defineEmits<{
  toggle: [];
}>();
</script>

<template>
  <li class="mrt-timeline__item mrt-timeline__toggle mrt-timeline-row-grid">
    <span class="mrt-timeline__time-spacer" aria-hidden="true" />
    <MrtTimelineNode segment="through" class="mrt-timeline__toggle-line" />
    <MrtExpandTrigger
      variant="link"
      align="start"
      full-width
      :expanded="expanded"
      :label="label"
      @toggle="emit('toggle')"
    />
  </li>
</template>

<style scoped>
.mrt-timeline__toggle {
  min-height: 2.5rem;
  padding: 0.15rem 0;
}

.mrt-timeline__toggle-line :deep(.mrt-timeline__node) {
  visibility: hidden;
}
</style>
