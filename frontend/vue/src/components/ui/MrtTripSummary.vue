<script setup lang="ts">
defineProps<{
  timeRange: string;
  route: string;
  date?: string;
  notice?: string;
  noticeWarn?: boolean;
  noticeCancelled?: boolean;
}>();
</script>

<template>
  <div class="mrt-trip-summary">
    <p
      class="mrt-trip-summary__time"
      :class="{ 'mrt-trip-summary__time--cancelled': noticeCancelled }"
    >
      {{ timeRange }}
    </p>
    <p class="mrt-trip-summary__route">{{ route }}</p>
    <p v-if="date" class="mrt-trip-summary__date">{{ date }}</p>
    <p
      v-if="notice"
      class="mrt-trip-summary__notice"
      :class="{
        'mrt-trip-summary__notice--warn': noticeWarn,
        'mrt-trip-summary__notice--cancelled': noticeCancelled,
      }"
    >
      {{ notice }}
    </p>
  </div>
</template>

<style scoped>
.mrt-trip-summary__time {
  margin: 0;
  font-size: clamp(1.35rem, 3vw, 1.85rem);
  line-height: 1.05;
  font-weight: 900;
}

.mrt-trip-summary__route {
  margin: 0.2rem 0 0;
  font-size: 1.2rem;
}

.mrt-trip-summary__date {
  margin: 0.2rem 0 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--mrt-color-neutral-600, #555);
}

.mrt-trip-summary__notice {
  margin: 0.35rem 0 0;
  font-size: 1rem;
  font-weight: 600;
}

.mrt-trip-summary__notice--warn,
.mrt-trip-summary__notice--cancelled,
.mrt-trip-summary__time--cancelled {
  color: var(--mrt-text-error, #b32d2e);
}

.mrt-trip-summary__time--cancelled {
  text-decoration: line-through;
}

@media (max-width: 48rem) {
  .mrt-trip-summary,
  .mrt-trip-summary__route {
    min-width: 0;
    max-width: 100%;
  }

  .mrt-trip-summary__route {
    overflow-wrap: anywhere;
  }
}
</style>
