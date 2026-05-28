<script setup lang="ts">
withDefaults(
  defineProps<{
    flush?: boolean;
    box?: boolean;
  }>(),
  {
    flush: false,
    box: false,
  },
);
</script>

<template>
  <div
    class="mrt-surface"
    :class="{
      'mrt-surface--flush': flush,
      'mrt-surface--box': box,
    }"
  >
    <slot />
  </div>
</template>
