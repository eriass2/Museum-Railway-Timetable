<script setup lang="ts">
import MrtAlert from './MrtAlert.vue';
import MrtButton from './MrtButton.vue';
import type { MrtUiContext } from './types';
import { isAdminContext } from './uiContext';

withDefaults(
  defineProps<{
    context?: MrtUiContext;
    loading?: boolean;
    error?: string;
    loadingText?: string;
    empty?: boolean;
    emptyText?: string;
    retryLabel?: string;
  }>(),
  {
    context: 'public',
    retryLabel: 'Försök igen',
  },
);

defineEmits<{ retry: [] }>();

function loadingClass(context: MrtUiContext): string {
  const base = 'mrt-empty mrt-empty--loading';
  return isAdminContext(context)
    ? `${base} mrt-admin-async__loading`
    : `${base} mrt-async__loading`;
}
</script>

<template>
  <p v-if="loading" :class="loadingClass(context)">
    {{ loadingText }}
  </p>
  <div v-else-if="error && context === 'admin'" class="mrt-admin-async__error notice notice-error">
    <p>{{ error }}</p>
    <div class="mrt-admin-async__retry">
      <MrtButton context="admin" variant="secondary" @click="$emit('retry')">
        {{ retryLabel }}
      </MrtButton>
    </div>
  </div>
  <MrtAlert v-else-if="error" variant="error" :context="context" live="assertive">
    {{ error }}
  </MrtAlert>
  <MrtAlert v-else-if="empty && emptyText" variant="info" :context="context">
    {{ emptyText }}
  </MrtAlert>
  <slot v-else />
</template>

<style scoped>
.mrt-admin-async__retry {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}
</style>
