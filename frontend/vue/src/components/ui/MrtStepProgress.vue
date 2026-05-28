<script setup lang="ts">
import type { MrtStepProgressItem } from './types';

withDefaults(
  defineProps<{
    items: MrtStepProgressItem[];
    navAriaLabel: string;
    readonly?: boolean;
  }>(),
  { readonly: true },
);
</script>

<template>
  <nav
    class="mrt-step-nav"
    :class="{ 'mrt-step-nav--readonly': readonly }"
    :aria-label="navAriaLabel"
  >
    <ol class="mrt-step-progress" role="list">
      <li
        v-for="item in items"
        :key="item.key"
        class="mrt-step-progress__item"
        :class="{ 'is-active': item.active, 'is-done': item.done }"
        :aria-current="item.active ? 'step' : undefined"
        :tabindex="item.active ? -1 : undefined"
      >
        {{ item.label }}
      </li>
    </ol>
  </nav>
</template>
