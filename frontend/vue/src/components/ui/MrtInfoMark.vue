<script setup lang="ts">
withDefaults(
  defineProps<{
    /** Screen-reader label when the icon conveys information. */
    label?: string;
    size?: 'sm' | 'md';
  }>(),
  { size: 'sm' },
);
</script>

<template>
  <span
    class="mrt-info-mark"
    :class="`mrt-info-mark--${size}`"
    :role="label ? 'img' : undefined"
    :aria-label="label"
    :aria-hidden="label ? undefined : true"
  >
    <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="8" cy="8" r="7.25" fill="currentColor" />
      <circle cx="8" cy="4.75" r="1" fill="#fff" />
      <path
        d="M8 7v4.25"
        stroke="#fff"
        stroke-width="1.5"
        stroke-linecap="round"
      />
    </svg>
  </span>
</template>

<style scoped>
.mrt-info-mark {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  vertical-align: middle;
  color: var(--mrt-color-brand-green, #296310);
  line-height: 0;
}

.mrt-info-mark--sm {
  width: 0.95rem;
  height: 0.95rem;
  margin-left: 0.2rem;
}

.mrt-info-mark--md {
  width: 1.05rem;
  height: 1.05rem;
}

.mrt-info-mark svg {
  display: block;
  width: 100%;
  height: 100%;
}
</style>
