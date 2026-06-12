<script setup lang="ts">
import MrtVisuallyHidden from './MrtVisuallyHidden.vue';

defineProps<{
  categoryKeys: string[];
  visibleTypes: string[];
  activeType: string;
  categories: Record<string, string>;
  tickets: Record<string, string>;
  typeColumnSr?: string;
  title: string;
  priceForCategory: (catKey: string, ticketType: string) => string;
}>();
</script>

<template>
  <div class="mrt-price-block__table-wrap">
    <table class="mrt-table mrt-price-block__table">
      <thead>
        <tr>
          <th scope="col" class="mrt-price-block__corner">
            <MrtVisuallyHidden>{{ typeColumnSr || title }}</MrtVisuallyHidden>
          </th>
          <th v-for="ck in categoryKeys" :key="ck" scope="col">
            {{ categories[ck] || ck }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="tk in visibleTypes"
          :key="tk"
          :class="{ 'mrt-price-block__row--active': tk === activeType }"
        >
          <th scope="row">{{ tickets[tk] || tk }}</th>
          <td
            v-for="ck in categoryKeys"
            :key="ck"
            :data-label="categories[ck] || ck"
          >
            {{ priceForCategory(ck, tk) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.mrt-price-block__table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.mrt-price-block__table {
  width: 100%;
  min-width: 0;
  border-collapse: collapse;
  table-layout: fixed;
  color: inherit;
}

.mrt-price-block__table :deep(th),
.mrt-price-block__table :deep(td) {
  padding: 0.5rem 0.4rem;
  text-align: left;
  border: 0;
  font-size: clamp(0.85rem, 2.5vw, 1rem);
  line-height: 1.3;
  vertical-align: top;
  overflow-wrap: anywhere;
}

.mrt-price-block__corner {
  width: 26%;
}

.mrt-price-block__table :deep(thead th:not(.mrt-price-block__corner)) {
  font-weight: 700;
}

.mrt-price-block__table :deep(tbody tr) {
  background: var(--mrt-color-neutral-100, #ececec);
}

.mrt-price-block__table :deep(th[scope='row']) {
  font-weight: 900;
}

.mrt-price-block__row--active :deep(th),
.mrt-price-block__row--active :deep(td) {
  font-weight: 900;
  background: color-mix(in srgb, var(--mrt-wizard-yellow) 40%, #fff);
}

@media (max-width: 32rem) {
  .mrt-price-block__table-wrap {
    overflow-x: visible;
  }

  .mrt-price-block__table :deep(thead) {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  .mrt-price-block__table,
  .mrt-price-block__table :deep(tbody),
  .mrt-price-block__table :deep(tr),
  .mrt-price-block__table :deep(th),
  .mrt-price-block__table :deep(td) {
    display: block;
    width: 100%;
  }

  .mrt-price-block__table :deep(tbody tr) {
    margin-bottom: 0.5rem;
    padding: 0.65rem 0.75rem;
    border-radius: 0;
  }

  .mrt-price-block__table :deep(th[scope='row']) {
    margin-bottom: 0.35rem;
    padding-bottom: 0.35rem;
    border-bottom: 1px solid var(--mrt-color-neutral-300, #ccc);
  }

  .mrt-price-block__table :deep(td) {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.35rem 0;
  }

  .mrt-price-block__table :deep(td)::before {
    content: attr(data-label);
    flex: 1 1 55%;
    font-weight: 700;
    color: var(--mrt-color-neutral-700, #444);
  }
}
</style>
