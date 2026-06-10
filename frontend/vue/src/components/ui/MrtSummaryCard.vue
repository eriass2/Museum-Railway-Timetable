<script setup lang="ts">
import MrtHeading from './MrtHeading.vue';

defineProps<{
  heading?: string;
}>();
</script>

<template>
  <article class="mrt-summary-card">
    <MrtHeading v-if="heading || $slots.heading" level="h4" size="md" class="mrt-summary-card__heading">
      <slot name="heading">{{ heading }}</slot>
    </MrtHeading>
    <slot />
  </article>
</template>
