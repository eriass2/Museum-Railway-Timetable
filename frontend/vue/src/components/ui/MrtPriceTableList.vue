<script setup lang="ts">
withDefaults(
  defineProps<{
    categoryKeys: string[];
    categories: Record<string, string>;
    valueForCategory: (catKey: string) => string;
    variant?: 'default' | 'summary';
  }>(),
  { variant: 'default' },
);
</script>

<template>
  <dl class="mrt-price-list" :class="{ 'mrt-price-list--summary': variant === 'summary' }">
    <div v-for="ck in categoryKeys" :key="ck" class="mrt-price-list__row">
      <dt class="mrt-price-list__label">{{ categories[ck] || ck }}</dt>
      <dd class="mrt-price-list__value">{{ valueForCategory(ck) }}</dd>
    </div>
  </dl>
</template>

<style scoped>
.mrt-price-list {
  margin: 0;
  padding: 0.65rem 0.85rem;
  border-radius: 0;
  background: var(--mrt-color-neutral-100, #ececec);
}

.mrt-price-list__row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.4rem 0;
  border-bottom: 1px solid var(--mrt-color-neutral-200, #d8d8d8);
}

.mrt-price-list__row:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.mrt-price-list__row:first-child {
  padding-top: 0;
}

.mrt-price-list__label {
  margin: 0;
  font-size: clamp(0.9rem, 2.5vw, 1rem);
  font-weight: 700;
  color: var(--mrt-color-neutral-700, #444);
}

.mrt-price-list__value {
  margin: 0;
  font-size: clamp(0.95rem, 2.5vw, 1.05rem);
  font-weight: 900;
  color: inherit;
  text-align: right;
  white-space: nowrap;
}

@media (max-width: 48rem) {
  .mrt-price-list--summary {
    padding: 0;
    border-radius: 0;
    background: transparent;
  }

  .mrt-price-list--summary .mrt-price-list__row {
    padding: 0.55rem 0;
    border-bottom-color: var(--mrt-color-neutral-300, #ccc);
  }

  .mrt-price-list--summary .mrt-price-list__label,
  .mrt-price-list--summary .mrt-price-list__value {
    color: var(--mrt-wizard-text, #151515);
    font-weight: 700;
  }
}
</style>
