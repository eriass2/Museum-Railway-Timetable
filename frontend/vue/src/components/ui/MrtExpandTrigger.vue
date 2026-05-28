<script setup lang="ts">
defineOptions({ inheritAttrs: false });

defineProps<{
  expanded: boolean;
  label: string;
}>();

defineEmits<{ toggle: [] }>();
</script>

<template>
  <button
    type="button"
    class="mrt-expand-trigger"
    :class="{ 'is-expanded': expanded }"
    :aria-expanded="expanded"
    v-bind="$attrs"
    @click="$emit('toggle')"
  >
    {{ label }}
    <span class="mrt-expand-trigger__chevron" aria-hidden="true" />
  </button>
</template>
