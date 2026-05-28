<script setup lang="ts">
defineProps<{
  backLabel: string;
  contextLine: string;
}>();

defineEmits<{ back: [] }>();
</script>

<template>
  <header class="mrt-step-header">
    <button type="button" class="mrt-step-header__back" @click="$emit('back')">
      {{ backLabel }}
    </button>
    <p class="mrt-step-header__context">{{ contextLine }}</p>
  </header>
</template>
