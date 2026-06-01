<script setup lang="ts">
import TrainTypeIcon from '../TrainTypeIcon.vue';

defineProps<{
  iconKey?: string;
  name?: string;
}>();
</script>

<template>
  <span class="admin-train-type-cell">
    <TrainTypeIcon v-if="iconKey" :icon-key="iconKey" :label="name || iconKey" />
    <span>{{ name || '—' }}</span>
  </span>
</template>

<style scoped>
.admin-train-type-cell {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
</style>
