<script setup lang="ts" generic="T extends string">
import type { MrtSegmentedOption } from './types';

const model = defineModel<T>({ required: true });

defineProps<{
  options: MrtSegmentedOption<T>[];
  legend?: string;
}>();
</script>

<template>
  <fieldset class="mrt-segmented-field">
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
