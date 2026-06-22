<script setup lang="ts">
import type { MrtStepProgressItem } from './types';

withDefaults(
  defineProps<{
    items: MrtStepProgressItem[];
    navAriaLabel: string;
    readonly?: boolean;
    stepGoToAria?: (label: string) => string;
  }>(),
  { readonly: true },
);

const emit = defineEmits<{
  select: [key: string];
}>();

function isClickable(item: MrtStepProgressItem, readonly: boolean): boolean {
  return !readonly && Boolean(item.done) && !item.active;
}

function stepAria(
  item: MrtStepProgressItem,
  formatter: ((label: string) => string) | undefined,
): string {
  return formatter ? formatter(item.label) : item.label;
}
</script>

<template>
  <nav
    class="mrt-step-nav"
    :class="{ 'mrt-step-nav--readonly': readonly }"
    :aria-label="navAriaLabel"
  >
    <ol class="mrt-step-progress" role="list">
      <li v-for="item in items" :key="item.key" class="mrt-step-progress__wrap">
        <button
          type="button"
          class="mrt-step-progress__item"
          :class="{ 'is-active': item.active, 'is-done': item.done }"
          :disabled="!isClickable(item, readonly)"
          :aria-current="item.active ? 'step' : undefined"
          :aria-label="isClickable(item, readonly) ? stepAria(item, stepGoToAria) : undefined"
          @click="emit('select', item.key)"
        >
          {{ item.label }}
        </button>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
@import './mrtFocusRing.css';

.mrt-step-nav {
  margin: 0 0 var(--mrt-step-nav-margin-bottom, 0.75rem);
  width: 100%;
  max-width: 100%;
  min-width: 0;
  overflow: visible;
}

.mrt-step-nav--readonly {
  pointer-events: none;
  user-select: none;
}

.mrt-step-progress {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.35rem;
  width: 100%;
  max-width: 100%;
  list-style: none;
  margin: 0;
  padding: 0;
}

.mrt-step-progress__wrap {
  display: block;
  min-width: 0;
}

/* Odd step count (tur/retur): last step spans full width. */
.mrt-step-progress__wrap:last-child:nth-child(odd) {
  grid-column: 1 / -1;
}

.mrt-step-progress__item {
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  min-width: 0;
  min-height: 2rem;
  margin: 0;
  padding: 0.35rem 0.65rem;
  border: 2px solid var(--mrt-color-border-on-dark, rgba(255, 255, 255, 0.35));
  border-radius: 0;
  background: var(--mrt-color-green-800);
  color: var(--mrt-color-on-dark-muted, rgba(255, 255, 255, 0.85));
  font-family: inherit;
  font-size: 0.8125rem;
  font-weight: 700;
  line-height: 1.25;
  text-align: center;
  appearance: none;
  box-shadow: none;
  cursor: default;
}

.mrt-step-progress__item.is-done:not(.is-active) {
  color: var(--mrt-color-on-dark, #fff);
  border-color: var(--mrt-wizard-yellow, var(--mrt-color-accent-600));
}

.mrt-step-progress__item.is-done:not(.is-active):not(:disabled) {
  cursor: pointer;
}

.mrt-step-progress__item.is-done:not(.is-active):not(:disabled):hover {
  background: color-mix(
    in srgb,
    var(--mrt-wizard-yellow, var(--mrt-color-accent-600)) 18%,
    var(--mrt-color-green-800)
  );
}

.mrt-step-progress__item.is-active {
  color: var(--mrt-color-on-accent, #1a1a1a);
  background: var(--mrt-wizard-yellow, var(--mrt-color-accent-600));
  border-color: var(--mrt-color-accent-700, #c9a01a);
}

.mrt-step-progress__item:focus-visible {
  outline-offset: -2px;
}

.mrt-step-progress__item:disabled {
  opacity: 1;
}

@media (min-width: 48.0625rem) {
  .mrt-step-progress {
    grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
  }

  .mrt-step-progress__wrap:last-child:nth-child(odd) {
    grid-column: auto;
  }
}

@media (max-width: 48rem) {
  .mrt-step-nav {
    overflow-x: visible;
  }

  .mrt-step-progress__item {
    min-height: 2.75rem;
    font-size: 0.7rem;
    line-height: 1.2;
    padding: 0.35rem 0.45rem;
    white-space: normal;
  }
}
</style>
