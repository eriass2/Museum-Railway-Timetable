<script setup lang="ts">
defineProps<{
  title: string;
  lead?: string;
}>();
</script>

<template>
  <header class="admin-page-header">
    <h1>{{ title }}</h1>
    <p v-if="lead" class="admin-page-header__lead">{{ lead }}</p>
    <slot />
  </header>
</template>

<style scoped>
.admin-page-header {
  margin: 0 0 12px;
}

.admin-page-header h1 {
  margin: 0;
}

.admin-page-header__lead {
  margin: 8px 0 0;
  max-width: 52rem;
  color: var(--mrt-admin-text-muted, #50575e);
  font-size: 14px;
  line-height: 1.5;
}
</style>
