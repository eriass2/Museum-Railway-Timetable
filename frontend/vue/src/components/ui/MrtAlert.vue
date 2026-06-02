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
</script>

<template>
  <p
    v-if="context === 'admin'"
    :class="adminClass"
    :role="role"
    :aria-live="live === 'off' ? undefined : live"
  >
    <slot />
  </p>
  <div
    v-else
    :class="publicClass"
    role="alert"
    :aria-live="live === 'off' ? undefined : live"
  >
    <slot />
  </div>
</template>
