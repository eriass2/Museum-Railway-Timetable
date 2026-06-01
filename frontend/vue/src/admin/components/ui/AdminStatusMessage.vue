<script setup lang="ts">
withDefaults(
  defineProps<{
    message?: string;
    type?: 'success' | 'error' | 'info' | 'warning';
  }>(),
  { type: 'success' },
);

const noticeClass: Record<string, string> = {
  success: 'notice notice-success',
  error: 'notice notice-error',
  info: 'notice notice-info',
  warning: 'notice notice-warning',
};
</script>

<template>
  <p v-if="message" :class="noticeClass[type]" role="status">{{ message }}</p>
</template>
