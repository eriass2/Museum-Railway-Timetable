<script setup lang="ts">
defineProps<{
  message: string;
  hint?: string;
}>();
</script>

<template>
  <p class="mrt-status-message" role="status">
    {{ message }}
    <span v-if="hint" class="mrt-status-message__hint">{{ hint }}</span>
  </p>
</template>

<style scoped>
.mrt-status-message {
  margin: 0;
  padding: 0.75rem 1.25rem 1rem;
  text-align: center;
  font-size: 0.95rem;
  font-weight: 700;
  border-top: 1px solid var(--mrt-color-neutral-200, #e0e0e0);
}

.mrt-status-message__hint {
  display: block;
  margin-top: 0.35rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--mrt-color-neutral-600, #555);
}
</style>
