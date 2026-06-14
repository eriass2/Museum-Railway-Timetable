<script setup lang="ts">
import MrtButton from './MrtButton.vue';
import type { MrtPublicButtonVariant } from './types';

withDefaults(
  defineProps<{
    href?: string;
    type?: 'button' | 'submit';
    variant?: MrtPublicButtonVariant;
    disabled?: boolean;
    size?: 'default' | 'search' | 'summary' | 'trip-card';
  }>(),
  {
    type: 'button',
    variant: 'primary',
    disabled: false,
    size: 'default',
  },
);

defineEmits<{ click: [] }>();
</script>

<template>
  <MrtButton
    context="public"
    :href="href"
    :type="type"
    :variant="variant"
    :disabled="disabled"
    :class="size !== 'default' ? `mrt-accent-btn--size-${size}` : undefined"
    @click="$emit('click')"
  >
    <slot />
  </MrtButton>
</template>
