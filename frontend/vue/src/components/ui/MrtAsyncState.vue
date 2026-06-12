<script setup lang="ts">
import MrtAlert from './MrtAlert.vue';
import MrtButton from './MrtButton.vue';
import type { MrtUiContext } from './types';
import { mrtAsyncLoadingClass } from './uiContext';

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
</script>

<template>
  <p v-if="loading" :class="mrtAsyncLoadingClass(context)">
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
.mrt-empty {
  padding: var(--mrt-spacing-sm);
  color: var(--mrt-text-tertiary);
  font-style: italic;
}

.mrt-empty--loading {
  text-align: center;
  padding: var(--mrt-spacing-xl, 1.5rem);
  color: var(--mrt-text-secondary);
}

.mrt-empty--loading::before {
  content: "";
  display: inline-block;
  width: 1em;
  height: 1em;
  margin-right: var(--mrt-spacing-sm);
  border: 2px solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  vertical-align: -0.15em;
  animation: mrt-loading-spin 0.75s linear infinite;
}

@keyframes mrt-loading-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (prefers-reduced-motion: reduce) {
  .mrt-empty--loading::before {
    animation: none;
    border-right-color: currentColor;
    opacity: 0.65;
  }
}

.mrt-async__loading {
  text-align: center;
  padding: var(--mrt-spacing-xl, 1.5rem);
}

.mrt-admin-async__retry {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}
</style>
