<script setup lang="ts">
import MrtHeading from './MrtHeading.vue';

defineProps<{
  title?: string;
  notice?: string;
  noticeLabel?: string;
  noticeCancelled?: boolean;
  transferText?: string;
}>();
</script>

<template>
  <div class="mrt-detail-segment mrt-mb-sm">
    <MrtHeading v-if="title" level="h4" size="md" class="mrt-detail-segment__title">
      {{ title }}
    </MrtHeading>
    <p
      v-if="notice"
      class="mrt-detail-segment__notice"
      :class="{ 'mrt-detail-segment__notice--cancelled': noticeCancelled }"
    >
      <strong v-if="noticeLabel">{{ noticeLabel }}:</strong> {{ notice }}
    </p>
    <div v-if="$slots.meta" class="mrt-detail-segment__meta">
      <slot name="meta" />
    </div>
    <slot />
    <p v-if="transferText" class="mrt-detail-segment__transfer">
      {{ transferText }}
    </p>
  </div>
</template>

<style scoped>
.mrt-detail-segment__notice {
  margin: 0.2rem 0 0.4rem;
  color: #151515;
  font-size: 0.95rem;
}

.mrt-detail-segment__notice--cancelled {
  color: var(--mrt-text-error, #b32d2e);
  font-weight: 700;
}

.mrt-detail-segment__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 0.8rem;
  align-items: center;
  margin: 0 0 0.55rem var(--mrt-tl-content-start, 0);
}

.mrt-detail-segment__transfer {
  margin: 0.45rem 0 0.8rem var(--mrt-tl-content-start, 0);
  color: #333333;
  font-size: 1.1rem;
  font-weight: 700;
}
</style>
