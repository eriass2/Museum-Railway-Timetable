<script setup lang="ts">
defineProps<{
  backLabel: string;
  contextLine: string;
  title?: string;
}>();

const emit = defineEmits<{ back: [] }>();
</script>

<template>
  <header class="mrt-journey-wizard__step-head">
    <button
      type="button"
      class="mrt-journey-wizard__back"
      @click="emit('back')"
    >
      {{ backLabel }}
    </button>
    <p class="mrt-journey-wizard__context">{{ contextLine }}</p>
  </header>
  <h3 v-if="title" class="mrt-journey-wizard__step-title">{{ title }}</h3>
  <slot />
</template>
