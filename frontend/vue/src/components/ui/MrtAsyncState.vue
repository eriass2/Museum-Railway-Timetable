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
      <MrtButton
        v-if="retryLabel"
        context="admin"
        variant="secondary"
        @click="$emit('retry')"
      >
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
@import './mrtSpinnerStyles.css';

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

.mrt-async__loading {
  text-align: center;
  padding: var(--mrt-spacing-xl, 1.5rem);
}

.mrt-admin-async__loading {
  text-align: center;
  padding: var(--mrt-spacing-lg) var(--mrt-spacing-md);
  color: var(--mrt-admin-text-muted);
  font-style: normal;
}

.mrt-admin-async__retry {
  display: flex;
  flex-wrap: wrap;
  gap: var(--mrt-admin-gap-sm);
  margin-top: var(--mrt-admin-gap-sm);
}

.mrt-admin-async__error p {
  margin: 0 0 var(--mrt-admin-gap-sm);
}

.mrt-admin-async__error p:last-child {
  margin-bottom: 0;
}
</style>
