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

<style scoped>
.mrt-surface {
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-wizard-text, #151515);
  border: 0;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
  padding: clamp(1.25rem, 3vw, 2rem);
  box-sizing: border-box;
}

.mrt-surface--flush {
  padding: 0;
}

.mrt-surface--box {
  box-shadow: none;
  border: 1px solid var(--mrt-box-border, var(--mrt-border-default));
  border-radius: var(--mrt-radius-sm, 0);
}

@media (max-width: 48rem) {
  .mrt-surface:not(.mrt-surface--flush) {
    padding: 0.85rem 0.75rem;
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
  }
}
</style>
