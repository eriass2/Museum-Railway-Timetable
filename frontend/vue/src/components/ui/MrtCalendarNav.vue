<script setup lang="ts">
withDefaults(
  defineProps<{
    monthTitle: string;
    mode?: 'buttons' | 'links';
    prevHref?: string;
    nextHref?: string;
    prevText?: string;
    nextText?: string;
    prevAria?: string;
    nextAria?: string;
    todayLabel?: string;
  }>(),
  {
    mode: 'buttons',
    prevHref: '#',
    nextHref: '#',
    prevText: '',
    nextText: '',
    prevAria: 'Föregående månad',
    nextAria: 'Nästa månad',
    todayLabel: '',
  },
);

const emit = defineEmits<{
  prev: [];
  next: [];
  today: [];
}>();
</script>

<template>
  <div class="mrt-calendar-nav" role="navigation">
    <template v-if="mode === 'links'">
      <a class="mrt-calendar-nav__link mrt-calendar-nav__prev" :href="prevHref || '#'">
        <span aria-hidden="true">‹</span>
        {{ prevText }}
      </a>
      <h2 class="mrt-calendar-nav__title">{{ monthTitle }}</h2>
      <a class="mrt-calendar-nav__link mrt-calendar-nav__next" :href="nextHref || '#'">
        {{ nextText }}
        <span aria-hidden="true">›</span>
      </a>
    </template>
    <template v-else>
      <button
        type="button"
        class="mrt-calendar-nav__prev"
        :aria-label="prevAria"
        @click="emit('prev')"
      >
        ‹
      </button>
      <p class="mrt-calendar-nav__title" aria-live="polite">{{ monthTitle }}</p>
      <button
        type="button"
        class="mrt-calendar-nav__next"
        :aria-label="nextAria"
        @click="emit('next')"
      >
        ›
      </button>
      <button
        v-if="todayLabel"
        type="button"
        class="mrt-calendar-nav__today"
        @click="emit('today')"
      >
        {{ todayLabel }}
      </button>
    </template>
  </div>
</template>

<style scoped>
@import './mrtFocusRing.css';

.mrt-calendar-nav {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.65rem;
  padding: 1rem 1.25rem 0.75rem;
}

.mrt-calendar-nav__title {
  flex: 1;
  margin: 0;
  text-align: center;
  font-size: clamp(1.35rem, 2.5vw, 1.75rem);
  font-weight: 700;
  line-height: 1.1;
}

.mrt-calendar-nav__prev,
.mrt-calendar-nav__next {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.35rem;
  height: 2.35rem;
  padding: 0;
  border: 2px solid var(--mrt-color-border-on-surface, #ccc);
  border-radius: var(--mrt-radius-sm, 0);
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-color-green-700, #1f4d2e);
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
  min-height: 0;
  text-decoration: none;
  cursor: pointer;
  box-sizing: border-box;
}

.mrt-calendar-nav__prev:hover,
.mrt-calendar-nav__next:hover,
.mrt-calendar-nav__link:hover {
  background: var(--mrt-color-neutral-200, #eee);
}

.mrt-calendar-nav__link {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.7rem;
  border: 1px solid var(--mrt-border-default, #ccc);
  border-radius: var(--mrt-radius-sm, 0);
  background: var(--mrt-bg-lighter, #f5f5f5);
  color: var(--mrt-text-secondary, #333);
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none;
}

.mrt-calendar-nav__today {
  margin-left: auto;
  padding: 0.35rem 0.7rem;
  border: 2px solid var(--mrt-color-border-on-surface, #ccc);
  border-radius: var(--mrt-radius-sm, 0);
  background: var(--mrt-wizard-surface, #fff);
  color: var(--mrt-color-green-700, #1f4d2e);
  font-size: 0.95rem;
  font-weight: 700;
  min-height: 0;
  cursor: pointer;
}

@media (max-width: 48rem) {
  .mrt-calendar-nav {
    flex-wrap: wrap;
    padding: 0.9rem 0.9rem 0;
  }
}

@media (max-width: 40rem) {
  .mrt-calendar-nav {
    padding: 0.75rem 0.5rem 0.55rem;
    gap: 0.5rem;
  }

  .mrt-calendar-nav__title {
    font-size: clamp(1.2rem, 5vw, 1.55rem);
  }

  .mrt-calendar-nav__prev,
  .mrt-calendar-nav__next {
    width: 2.65rem;
    height: 2.65rem;
    font-size: 1.4rem;
  }
}
</style>
