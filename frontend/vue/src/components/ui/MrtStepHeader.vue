<script setup lang="ts">
withDefaults(
  defineProps<{
    backLabel: string;
    contextLine: string;
    /** Tighter gap below header (date step). */
    spacing?: 'default' | 'tight';
  }>(),
  { spacing: 'default' },
);

defineEmits<{ back: [] }>();
</script>

<template>
  <header
    class="mrt-step-header"
    :class="{ 'mrt-step-header--tight': spacing === 'tight' }"
  >
    <button type="button" class="mrt-step-header__back" @click="$emit('back')">
      {{ backLabel }}
    </button>
    <p class="mrt-step-header__context">{{ contextLine }}</p>
  </header>
</template>

<style scoped>
.mrt-step-header {
  display: grid;
  gap: 0.5rem;
  margin-bottom: 0.85rem;
  min-width: 0;
  max-width: 100%;
}

.mrt-step-header--tight {
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.mrt-step-header__back {
  justify-self: start;
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--mrt-color-accent-500);
  font-size: 1.15rem;
  font-weight: 700;
  min-height: 0;
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 0.12em;
}

.mrt-step-header__back:hover {
  color: var(--mrt-color-on-dark, #fff);
}

.mrt-step-header__back:focus-visible {
  outline: 3px solid var(--mrt-wizard-focus);
  outline-offset: 3px;
}

.mrt-step-header__context {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--mrt-color-on-dark-muted, rgba(255, 255, 255, 0.85));
  min-width: 0;
  max-width: 100%;
}

@media (max-width: 48rem) {
  .mrt-step-header__context {
    overflow-wrap: anywhere;
  }
}
</style>
