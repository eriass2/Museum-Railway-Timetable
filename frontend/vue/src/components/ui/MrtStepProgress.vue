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
      <li v-for="item in items" :key="item.key" class="mrt-step-progress__wrap" role="presentation">
        <button
          v-if="isClickable(item, readonly)"
          type="button"
          class="mrt-step-progress__item is-done"
          :aria-label="stepAria(item, stepGoToAria)"
          @click="emit('select', item.key)"
        >
          {{ item.label }}
        </button>
        <span
          v-else
          class="mrt-step-progress__item"
          :class="{ 'is-active': item.active, 'is-done': item.done }"
          :aria-current="item.active ? 'step' : undefined"
        >
          {{ item.label }}
        </span>
      </li>
    </ol>
  </nav>
</template>
