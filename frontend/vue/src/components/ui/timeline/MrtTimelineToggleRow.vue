<script setup lang="ts">
import MrtExpandTrigger from '../MrtExpandTrigger.vue';

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
    <span
      class="mrt-timeline__node-col mrt-timeline__node-col--empty"
      aria-hidden="true"
    />
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
  padding: 0.15rem 0;
}

.mrt-timeline__node-col--empty {
  width: var(--mrt-tl-node);
  justify-self: center;
}
</style>
