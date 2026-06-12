<script setup lang="ts">
withDefaults(
  defineProps<{
    timetableHref?: string;
    timetableLabel?: string;
    /** Link colors on white surface vs green/dark hero. */
    linkTone?: 'surface' | 'dark';
  }>(),
  { linkTone: 'surface' },
);
</script>

<template>
  <div
    class="mrt-route-layout"
    :class="{ 'mrt-route-layout--dark-links': linkTone === 'dark' }"
  >
    <div class="mrt-route-layout__stations">
      <slot name="stations" />
    </div>
    <slot />
    <p v-if="timetableHref" class="mrt-route-layout__link-wrap">
      <a
        class="mrt-route-layout__link"
        :href="timetableHref"
        target="_blank"
        rel="noopener noreferrer"
      >
        <slot name="timetable">{{ timetableLabel }}</slot>
      </a>
    </p>
  </div>
</template>

<style scoped>
.mrt-route-layout {
  width: min(100%, 36rem);
  margin-inline: auto;
}

.mrt-route-layout__stations {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.mrt-route-layout__link-wrap {
  margin: 1.25rem 0 0;
  text-align: center;
}

.mrt-route-layout__link {
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 0.15em;
  color: var(--mrt-color-green-700, #1f4d2e);
}

.mrt-route-layout__link:hover {
  color: var(--mrt-color-accent-700, #a88a10);
}

.mrt-route-layout--dark-links .mrt-route-layout__link {
  color: var(--mrt-color-on-dark-link, #f5e6a8);
}

.mrt-route-layout--dark-links .mrt-route-layout__link:hover {
  color: var(--mrt-color-on-dark, #ffffff);
}
</style>
