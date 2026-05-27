<script setup lang="ts">
defineProps<{
  backLabel: string;
  contextLine: string;
}>();

defineEmits<{ back: [] }>();
</script>

<template>
  <header class="mrt-journey-wizard__step-header">
    <button type="button" class="mrt-journey-wizard__back" @click="$emit('back')">
      {{ backLabel }}
    </button>
    <p class="mrt-journey-wizard__context">{{ contextLine }}</p>
  </header>
</template>
