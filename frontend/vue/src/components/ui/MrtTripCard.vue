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
        <div v-if="$slots.vehicles" class="mrt-trip-card__side-vehicles">
          <slot name="vehicles" />
        </div>
        <div v-if="$slots.duration" class="mrt-trip-card__duration">
          <slot name="duration" />
        </div>
        <div v-if="$slots.action" class="mrt-trip-card__side-action">
          <slot name="action" />
        </div>
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

@media (max-width: 48rem) {
  .mrt-trip-card__head {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.85rem 0.75rem;
  }

  .mrt-trip-card__copy,
  .mrt-trip-card__side {
    width: 100%;
    min-width: 0;
  }

  .mrt-trip-card__side {
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-areas:
      "vehicles vehicles"
      "duration action";
    align-items: center;
    gap: 0.55rem 0.75rem;
  }

  .mrt-trip-card__side-vehicles {
    grid-area: vehicles;
    width: 100%;
    min-width: 0;
  }

  .mrt-trip-card__side-action {
    grid-area: action;
    justify-self: end;
  }

  .mrt-trip-card__duration {
    grid-area: duration;
    margin: 0;
    font-size: 1.15rem;
  }
}

@media (max-width: 22.5rem) {
  .mrt-trip-card__side {
    grid-template-columns: minmax(0, 1fr);
    grid-template-areas:
      "vehicles"
      "duration"
      "action";
  }

  .mrt-trip-card__side-action {
    justify-self: stretch;
    width: 100%;
  }
}
</style>
