<script setup lang="ts">
import type { MrtDotColor } from './types';

defineProps<{
  color: MrtDotColor;
}>();
</script>

<template>
  <span class="mrt-dot" :class="`mrt-dot--${color}`" aria-hidden="true" />
</template>
