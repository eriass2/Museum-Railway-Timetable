<script setup lang="ts">
import MrtButton from './MrtButton.vue';
import type { MrtPublicButtonVariant } from './types';

withDefaults(
  defineProps<{
    href?: string;
    type?: 'button' | 'submit';
    variant?: MrtPublicButtonVariant;
    disabled?: boolean;
  }>(),
  {
    type: 'button',
    variant: 'primary',
    disabled: false,
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
    @click="$emit('click')"
  >
    <slot />
  </MrtButton>
</template>
