<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import type { MrtAdminButtonVariant, MrtPublicButtonVariant, MrtUiContext } from './types';
import { isAdminContext, mrtAdminButtonClass, mrtPublicButtonClass } from './uiContext';

defineOptions({ inheritAttrs: false });

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

const attrs = useAttrs();

const buttonClass = computed(() => {
  const base = isAdminContext(props.context)
    ? mrtAdminButtonClass(props.variant as MrtAdminButtonVariant, props.wide)
    : mrtPublicButtonClass(props.variant as MrtPublicButtonVariant);
  const extra = attrs.class;
  if (!extra) {
    return base;
  }
  return [base, extra];
});
</script>

<template>
  <a v-if="href" :href="href" :class="buttonClass" v-bind="attrs">
    <slot />
  </a>
  <button
    v-else
    :type="type"
    :class="buttonClass"
    :disabled="disabled"
    v-bind="attrs"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>
