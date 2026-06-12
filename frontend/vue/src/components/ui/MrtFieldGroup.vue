<script setup lang="ts">
import MrtVisuallyHidden from './MrtVisuallyHidden.vue';

defineProps<{
  label: string;
  inputId: string;
  hideLabel?: boolean;
}>();
</script>

<template>
  <div class="mrt-field">
    <MrtVisuallyHidden v-if="hideLabel">
      <label class="mrt-field__label" :for="inputId">{{ label }}</label>
    </MrtVisuallyHidden>
    <label v-else class="mrt-field__label" :for="inputId">{{ label }}</label>
    <slot />
  </div>
</template>

<style scoped>
.mrt-field {
  margin: 0;
}

.mrt-field__label {
  display: block;
  margin-bottom: 0.35rem;
  color: var(--mrt-wizard-text, #151515);
  font-size: 1.05rem;
  font-weight: 700;
}
</style>
