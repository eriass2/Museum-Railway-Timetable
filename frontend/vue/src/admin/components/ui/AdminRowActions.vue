<script setup lang="ts">
defineProps<{
  stack?: boolean;
}>();
</script>

<template>
  <div class="admin-row-actions" :class="{ 'admin-row-actions--stack': stack }">
    <slot />
  </div>
</template>

<style scoped>
.admin-row-actions {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
  gap: 6px;
}

.admin-row-actions--stack {
  flex-direction: column;
  align-items: stretch;
}

.admin-row-actions :deep(.button) {
  margin: 0;
  min-height: 30px;
  padding: 0 10px;
  line-height: 28px;
  font-size: 13px;
}

.admin-row-actions :deep(.button-link-delete) {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 30px;
  padding: 0 10px;
  border: 1px solid #d63638;
  border-radius: 3px;
  background: #fcf0f1;
  color: #b32d2e;
  text-decoration: none;
  font-size: 13px;
  line-height: 1.4;
}

.admin-row-actions--stack :deep(.button) {
  width: 100%;
  justify-content: center;
  text-align: center;
}
</style>
