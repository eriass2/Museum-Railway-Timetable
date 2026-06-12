<script setup lang="ts">
defineProps<{
  categoryKeys: string[];
  categories: Record<string, string>;
  valueForCategory: (catKey: string) => string;
}>();
</script>

<template>
  <dl class="mrt-price-list">
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
</style>
