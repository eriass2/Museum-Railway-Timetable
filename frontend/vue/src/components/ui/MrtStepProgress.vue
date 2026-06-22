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
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  justify-content: center;
  list-style: none;
  margin: 0;
  padding: 0;
}

.mrt-step-progress__wrap {
  display: contents;
}

.mrt-step-progress__item {
  box-sizing: border-box;
  display: inline-flex;
  align-items: center;
  justify-content: center;
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
  box-shadow: 0 0 0 2px transparent;
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
  box-shadow: 0 0 0 2px var(--mrt-wizard-focus);
}

.mrt-step-progress__item:disabled {
  opacity: 1;
}

@media (max-width: 48rem) {
  .mrt-step-nav {
    overflow-x: visible;
  }

  .mrt-step-progress {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.3rem;
    width: 100%;
    max-width: 100%;
  }

  .mrt-step-progress__wrap {
    display: block;
    flex: 1 1 auto;
    min-width: min(100%, 9.5rem);
    max-width: 100%;
  }

  .mrt-step-progress__item {
    width: 100%;
    min-width: 0;
    min-height: 2.75rem;
    font-size: 0.7rem;
    line-height: 1.2;
    padding: 0.35rem 0.45rem;
    white-space: normal;
  }
}
</style>
