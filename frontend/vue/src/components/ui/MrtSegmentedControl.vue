<script setup lang="ts" generic="T extends string">
import type { MrtSegmentedOption } from './types';

const model = defineModel<T>({ required: true });

withDefaults(
  defineProps<{
    options: MrtSegmentedOption<T>[];
    legend?: string;
    size?: 'default' | 'compact';
  }>(),
  { size: 'default' },
);
</script>

<template>
  <fieldset
    class="mrt-segmented-field"
    :class="{ 'mrt-segmented-field--compact': size === 'compact' }"
  >
    <legend v-if="legend" class="mrt-segmented-field__legend">{{ legend }}</legend>
    <div class="mrt-segmented" role="radiogroup">
      <button
        v-for="opt in options"
        :key="opt.value"
        type="button"
        class="mrt-segmented__option"
        role="radio"
        :aria-checked="model === opt.value"
        :class="{ 'is-active': model === opt.value }"
        @click="model = opt.value"
      >
        <slot name="option" :option="opt">
          {{ opt.label }}
        </slot>
      </button>
    </div>
  </fieldset>
</template>

<style scoped>
.mrt-segmented-field {
  border: 0;
  padding: 0;
  margin: 0 0 1.5rem;
}

.mrt-segmented-field__legend {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--mrt-wizard-text, #151515);
  font-size: 1.05rem;
  font-weight: 700;
}

.mrt-segmented {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0;
  width: 100%;
  border: 2px solid var(--mrt-color-border-on-surface, #767676);
}

.mrt-segmented__option {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  min-height: 3.25rem;
  margin: 0;
  padding: 0.5rem 0.75rem;
  border: 0;
  border-radius: 0;
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #141414);
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
  cursor: pointer;
  white-space: normal;
  text-align: center;
}

.mrt-segmented__option + .mrt-segmented__option {
  border-left: 2px solid var(--mrt-color-border-on-surface, #767676);
}

.mrt-segmented__option.is-active {
  background: var(--mrt-wizard-yellow, var(--mrt-color-accent-600));
  color: var(--mrt-color-on-accent);
}

.mrt-segmented__option:not(.is-active):hover {
  background: color-mix(in srgb, var(--mrt-wizard-yellow, var(--mrt-color-accent-600)) 16%, #fff);
}

.mrt-segmented-field--compact {
  margin-bottom: 1.25rem;
}

.mrt-segmented-field--compact .mrt-segmented-field__legend {
  margin-bottom: 0.4rem;
  font-size: 0.95rem;
}

.mrt-segmented-field--compact .mrt-segmented__option {
  min-height: 2.5rem;
  padding: 0.35rem 0.5rem;
  gap: 0.4rem;
  font-size: 0.875rem;
}

@media (max-width: 48rem) {
  .mrt-segmented {
    grid-template-columns: 1fr;
  }

  .mrt-segmented__option + .mrt-segmented__option {
    border-left: 0;
    border-top: 2px solid var(--mrt-color-border-on-surface, #767676);
  }

  .mrt-segmented__option {
    justify-content: flex-start;
    padding: 0.65rem 0.85rem;
    font-size: 0.975rem;
  }

  .mrt-segmented-field--compact .mrt-segmented__option {
    font-size: 0.875rem;
    padding: 0.35rem 0.5rem;
  }
}
</style>
