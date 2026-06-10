<script setup lang="ts">
withDefaults(
  defineProps<{
    align?: 'start' | 'end';
  }>(),
  { align: 'start' },
);
</script>

<template>
  <div class="admin-form-actions" :class="{ 'admin-form-actions--end': align === 'end' }">
    <slot />
  </div>
</template>

<style scoped>
.admin-form-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
}

.admin-form-actions--end {
  justify-content: flex-end;
}

.admin-form-actions :deep(.button) {
  margin: 0;
}
</style>
