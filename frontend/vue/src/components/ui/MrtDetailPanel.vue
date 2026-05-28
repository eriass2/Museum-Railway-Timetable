<script setup lang="ts">
defineProps<{
  multi?: boolean;
}>();
</script>

<template>
  <div class="mrt-detail-panel" :class="{ 'mrt-detail-panel--multi': multi }">
    <slot />
  </div>
</template>
