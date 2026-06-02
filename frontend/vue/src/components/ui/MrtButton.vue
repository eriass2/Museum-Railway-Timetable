<script setup lang="ts">
import { computed } from 'vue';
import type { MrtAdminButtonVariant, MrtPublicButtonVariant, MrtUiContext } from './types';
import { isAdminContext, mrtAdminButtonClass, mrtPublicButtonClass } from './uiContext';

const props = withDefaults(
  defineProps<{
    context?: MrtUiContext;
    /** Public: primary | select | secondary. Admin: primary | secondary | link | link-delete | small. */
    variant?: MrtPublicButtonVariant | MrtAdminButtonVariant;
    href?: string;
    type?: 'button' | 'submit';
    disabled?: boolean;
    /** Admin mobile: adds WordPress `widefat`. */
    wide?: boolean;
  }>(),
  {
    context: 'public',
    type: 'button',
    disabled: false,
    variant: 'primary',
    wide: false,
  },
);

defineEmits<{ click: [] }>();

const buttonClass = computed(() => {
  if (isAdminContext(props.context)) {
    return mrtAdminButtonClass(props.variant as MrtAdminButtonVariant, props.wide);
  }
  return mrtPublicButtonClass(props.variant as MrtPublicButtonVariant);
});
</script>

<template>
  <a v-if="href" :href="href" :class="buttonClass">
    <slot />
  </a>
  <button
    v-else
    :type="type"
    :class="buttonClass"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>
