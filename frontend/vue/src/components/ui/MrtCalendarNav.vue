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
