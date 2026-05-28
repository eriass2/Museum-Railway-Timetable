<script setup lang="ts">
defineProps<{
  expanded?: boolean;
}>();
</script>

<template>
  <article class="mrt-trip-card" :class="{ 'is-expanded': expanded }">
    <div class="mrt-trip-card__head">
      <div class="mrt-trip-card__copy">
        <slot name="copy" />
      </div>
      <div class="mrt-trip-card__side">
        <slot name="side" />
      </div>
    </div>
    <slot name="actions" />
    <slot />
  </article>
</template>
