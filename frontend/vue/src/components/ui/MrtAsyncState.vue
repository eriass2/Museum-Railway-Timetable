<script setup lang="ts">
import MrtAlert from './MrtAlert.vue';

defineProps<{
  loading?: boolean;
  error?: string;
  loadingText?: string;
  empty?: boolean;
  emptyText?: string;
}>();
</script>

<template>
  <p v-if="loading" class="mrt-empty mrt-empty--loading mrt-async__loading">
    {{ loadingText || 'Laddar...' }}
  </p>
  <MrtAlert v-else-if="error" variant="error" live="assertive">
    {{ error }}
  </MrtAlert>
  <MrtAlert v-else-if="empty && emptyText" variant="info">
    {{ emptyText }}
  </MrtAlert>
  <slot v-else />
</template>
