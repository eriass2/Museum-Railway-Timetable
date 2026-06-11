<script setup lang="ts">
import { computed } from 'vue';
import type { MrtAlertVariant, MrtUiContext } from './types';
import { isAdminContext, mrtAdminNoticeClass } from './uiContext';

const props = withDefaults(
  defineProps<{
    context?: MrtUiContext;
    variant?: MrtAlertVariant;
    live?: 'off' | 'polite' | 'assertive';
  }>(),
  {
    context: 'public',
    variant: 'info',
    live: 'off',
  },
);

const adminClass = computed(() =>
  isAdminContext(props.context) ? mrtAdminNoticeClass(props.variant) : '',
);

const publicClass = computed(() =>
  isAdminContext(props.context) ? '' : `mrt-ui-alert mrt-ui-alert--${props.variant}`,
);

const role = computed(() => (isAdminContext(props.context) ? 'status' : 'alert'));

const ariaLive = computed(() => (props.live === 'off' ? undefined : props.live));
</script>

<template>
  <p
    v-if="context === 'admin'"
    :class="adminClass"
    :role="role"
    :aria-live="ariaLive"
  >
    <slot />
  </p>
  <div
    v-else
    :class="publicClass"
    role="alert"
    :aria-live="ariaLive"
  >
    <slot />
  </div>
</template>

<style scoped>
.mrt-ui-alert {
  padding: var(--mrt-spacing-sm, 0.5rem);
  margin-bottom: var(--mrt-spacing-md, 0.75rem);
  border-radius: var(--mrt-radius-sm, 0);
}

.mrt-ui-alert--info {
  background: var(--mrt-info-bg, #e8f4fc);
  border-left: 4px solid var(--mrt-info-border, #2271b1);
}

.mrt-ui-alert--error {
  background: var(--mrt-bg-error, #fcf0f1);
  border-left: 4px solid var(--mrt-text-error, #b32d2e);
  color: var(--mrt-text-error, #b32d2e);
}

.mrt-ui-alert--warning {
  background: var(--mrt-warning-bg, #fcf9e8);
  border-left: 4px solid var(--mrt-warning-border, #dba617);
}
</style>
