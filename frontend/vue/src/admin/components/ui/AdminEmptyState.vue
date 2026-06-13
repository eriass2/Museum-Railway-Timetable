<script setup lang="ts">
defineProps<{
  title: string;
  message?: string;
}>();
</script>

<template>
  <div class="admin-empty-state">
    <p class="admin-empty-state__title">{{ title }}</p>
    <p v-if="message" class="admin-empty-state__message">{{ message }}</p>
    <div v-if="$slots.default" class="admin-empty-state__actions">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.admin-empty-state {
  padding: 20px 16px;
  text-align: center;
  border: 1px dashed var(--mrt-admin-border, #c3c4c7);
  border-radius: var(--mrt-admin-radius, 3px);
  background: var(--mrt-admin-surface-muted-bg, #f6f7f7);
}

.admin-empty-state__title {
  margin: 0;
  font-weight: 600;
  color: var(--mrt-admin-text, #1d2327);
}

.admin-empty-state__message {
  margin: 8px 0 0;
  color: var(--mrt-admin-text-muted, #50575e);
  font-size: 13px;
}

.admin-empty-state__actions {
  margin-top: 12px;
}
</style>
