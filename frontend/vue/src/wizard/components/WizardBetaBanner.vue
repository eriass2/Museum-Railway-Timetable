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

<style scoped>
.mrt-journey-wizard__beta {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 0.5rem 0.75rem;
  max-width: 46rem;
  margin: 0 auto 1rem;
  padding: 0.65rem 0.85rem;
  border: 1px solid color-mix(in srgb, var(--mrt-color-on-dark) 22%, transparent);
  border-radius: 0.35rem;
  background: color-mix(in srgb, var(--mrt-wizard-green-dark) 55%, #000);
  color: var(--mrt-color-on-dark-muted);
  font-size: 0.9rem;
  line-height: 1.45;
}

.mrt-journey-wizard__beta-badge {
  flex-shrink: 0;
  padding: 0.15rem 0.5rem;
  border-radius: 0.2rem;
  background: var(--mrt-color-accent-600, #ddd24c);
  color: var(--mrt-color-on-accent, #1a1a1a);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.mrt-journey-wizard__beta-text {
  margin: 0;
  flex: 1 1 12rem;
  min-width: 0;
}

.mrt-journey-wizard__beta-link {
  color: var(--mrt-color-on-dark);
  font-weight: 700;
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

.mrt-journey-wizard__beta-link:hover,
.mrt-journey-wizard__beta-link:focus-visible {
  color: #fff;
}
</style>
