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
