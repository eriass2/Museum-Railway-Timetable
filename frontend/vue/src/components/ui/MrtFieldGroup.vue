<script setup lang="ts">
defineProps<{
  label: string;
  inputId: string;
  hideLabel?: boolean;
}>();
</script>

<template>
  <div class="mrt-field">
    <label
      class="mrt-field__label"
      :class="{ 'mrt-sr-only': hideLabel }"
      :for="inputId"
    >{{ label }}</label>
    <slot />
  </div>
</template>
