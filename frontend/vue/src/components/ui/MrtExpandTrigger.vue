<script setup lang="ts">
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
    @click="$emit('toggle')"
  >
    {{ label }}
    <span class="mrt-expand-trigger__chevron" aria-hidden="true" />
  </button>
</template>
