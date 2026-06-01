<script setup lang="ts">
defineOptions({ inheritAttrs: false });

withDefaults(
  defineProps<{
    expanded: boolean;
    label: string;
    /** Full-width bar (trip card) or inline link (timeline stops). */
    variant?: 'bar' | 'link';
  }>(),
  { variant: 'bar' },
);

defineEmits<{ toggle: [] }>();
</script>

<template>
  <button
    type="button"
    class="mrt-expand-trigger"
    :class="{
      'is-expanded': expanded,
      'mrt-expand-trigger--link': variant === 'link',
    }"
    :aria-expanded="expanded"
    v-bind="$attrs"
    @click="$emit('toggle')"
  >
    {{ label }}
    <span class="mrt-expand-trigger__chevron" aria-hidden="true" />
  </button>
</template>
