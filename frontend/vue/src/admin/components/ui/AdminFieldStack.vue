<script setup lang="ts">
defineProps<{
  label?: string;
  labelFor?: string;
  description?: string;
}>();
</script>

<template>
  <p class="mrt-admin-trip-fields__field">
    <label v-if="label" :for="labelFor">{{ label }}</label>
    <slot />
    <span v-if="description" class="description">{{ description }}</span>
  </p>
</template>
