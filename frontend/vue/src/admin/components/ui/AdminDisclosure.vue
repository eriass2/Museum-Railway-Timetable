<script setup lang="ts">
defineProps<{
  summary?: string;
}>();
</script>

<template>
  <details class="admin-disclosure">
    <summary class="admin-disclosure__summary">{{ summary ?? 'Avancerat' }}</summary>
    <div class="admin-disclosure__body">
      <slot />
    </div>
  </details>
</template>

<style scoped>
.admin-disclosure {
  margin-top: 4px;
}

.admin-disclosure__summary {
  cursor: pointer;
  color: #2271b1;
  font-size: 13px;
  user-select: none;
}

.admin-disclosure__summary:hover,
.admin-disclosure__summary:focus {
  color: #135e96;
}

.admin-disclosure__body {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #f0f0f1;
}
</style>
