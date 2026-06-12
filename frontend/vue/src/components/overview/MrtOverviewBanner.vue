<script setup lang="ts">
defineProps<{
  label: string;
}>();
</script>

<template>
  <p class="mrt-ov-banner">
    {{ label }}
  </p>
</template>

<style scoped>
.mrt-ov-banner {
  margin: 0 0 var(--mrt-spacing-md, 1rem);
  padding: 0.65rem var(--mrt-spacing-md, 1rem);
  background: var(--mrt-ov-header-bg);
  color: var(--mrt-ov-header-fg);
  font-weight: 700;
  letter-spacing: 0.02em;
  text-align: center;
  text-transform: uppercase;
}

@media (max-width: 40rem) {
  .mrt-ov-banner {
    font-size: 0.9rem;
    padding-inline: 0.75rem;
  }
}
</style>
