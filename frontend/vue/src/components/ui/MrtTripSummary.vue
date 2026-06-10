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
