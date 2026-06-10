<script setup lang="ts">
defineProps<{
  active?: boolean;
}>();
</script>

<template>
  <tr class="admin-flash-row" :class="{ 'admin-flash-row--active': active }">
    <slot />
  </tr>
</template>

<style scoped>
.admin-flash-row--active :deep(td),
.admin-flash-row--active :deep(th) {
  animation: admin-flash-row 1.6s ease-out;
}

@keyframes admin-flash-row {
  0%,
  20% {
    background-color: #edfaef;
  }

  100% {
    background-color: inherit;
  }
}

@media (prefers-reduced-motion: reduce) {
  .admin-flash-row--active :deep(td),
  .admin-flash-row--active :deep(th) {
    animation: none;
    background-color: #edfaef;
  }
}
</style>
