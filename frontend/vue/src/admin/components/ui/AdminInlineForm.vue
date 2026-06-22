<script setup lang="ts">
defineProps<{
  tag?: 'div' | 'p';
}>();
</script>

<template>
  <component :is="tag ?? 'div'" class="admin-inline-form">
    <slot />
  </component>
</template>

<style scoped>
.admin-inline-form {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin: 0 0 12px;
}

.admin-inline-form :deep(.regular-text) {
  flex: 1 1 14rem;
  max-width: var(--mrt-max-narrow);
}

.admin-inline-form :deep(.button) {
  margin: 0;
}

@media (max-width: 782px) {
  .admin-inline-form {
    flex-direction: column;
    align-items: stretch;
  }

  .admin-inline-form :deep(.regular-text) {
    width: 100%;
    max-width: none;
  }

  .admin-inline-form :deep(.button) {
    width: 100%;
  }
}
</style>
