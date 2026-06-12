<script setup lang="ts">
defineProps<{
  expanded?: boolean;
}>();
</script>

<template>
  <article class="mrt-trip-card" :class="{ 'is-expanded': expanded }">
    <div class="mrt-trip-card__head">
      <div class="mrt-trip-card__copy">
        <slot name="copy" />
      </div>
      <div class="mrt-trip-card__side">
        <slot name="side" />
      </div>
    </div>
    <slot name="actions" />
    <slot />
  </article>
</template>

<style scoped>
.mrt-trip-card {
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #151515);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14);
}

.mrt-trip-card.is-expanded > .mrt-trip-card__head {
  background: #d6d6d6;
}

.mrt-trip-card__head {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 1rem;
  padding: 0.75rem 0.9rem;
  align-items: start;
}

.mrt-trip-card__side {
  display: grid;
  align-content: start;
  justify-items: end;
  gap: 0.35rem;
}

.mrt-trip-card__duration {
  font-size: 1.35rem;
  font-weight: 900;
  color: #3f3f3f;
  white-space: nowrap;
}
</style>
