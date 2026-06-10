<script setup lang="ts">
defineProps<{
  label: string;
  text: string;
  feedbackLabel?: string;
  feedbackUrl?: string;
}>();
</script>

<template>
  <aside class="mrt-journey-wizard__beta" role="note" :aria-label="label">
    <span class="mrt-journey-wizard__beta-badge">{{ label }}</span>
    <p class="mrt-journey-wizard__beta-text">
      {{ text }}
      <a
        v-if="feedbackUrl && feedbackLabel"
        :href="feedbackUrl"
        class="mrt-journey-wizard__beta-link"
      >
        {{ feedbackLabel }}
      </a>
    </p>
  </aside>
</template>
