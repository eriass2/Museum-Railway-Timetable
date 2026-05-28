<script setup lang="ts">
defineProps<{
  label: string;
  inputId: string;
}>();
</script>

<template>
  <div class="mrt-field">
    <label class="mrt-field__label" :for="inputId">{{ label }}</label>
    <slot />
  </div>
</template>
