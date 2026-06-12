<template>
  <div class="mrt-detail-panel">
    <slot />
  </div>
</template>

<style scoped>
.mrt-detail-panel {
  container-type: inline-size;
  container-name: mrt-detail;
  padding: 1.2rem 0.8rem 1rem;
  background: var(--mrt-wizard-surface, #fff);
  --mrt-tl-time: clamp(3.25rem, 8vw, 5.4rem);
  --mrt-tl-node: clamp(1rem, 2.5vw, 1.6rem);
  --mrt-tl-gap: clamp(0.35rem, 1vw, 0.65rem);
  --mrt-tl-content-start: calc(var(--mrt-tl-time) + var(--mrt-tl-gap) + var(--mrt-tl-node) + var(--mrt-tl-gap));
}

.mrt-detail-panel--multi {
  display: grid;
  gap: 1rem;
}

@container mrt-detail (max-width: 28rem) {
  :deep(.mrt-timeline) {
    --mrt-tl-time: clamp(3rem, 18cqi, 3.75rem);
    --mrt-tl-node: 1.1rem;
    --mrt-tl-gap: 0.4rem;
  }

  :deep(.mrt-timeline__row) {
    min-height: 2.15rem;
  }

  :deep(.mrt-timeline__node) {
    justify-self: center;
    width: var(--mrt-tl-node);
    height: var(--mrt-tl-node);
    margin-top: 0.15rem;
    border-width: 0.22rem;
  }

  :deep(.mrt-timeline__toggle .mrt-expand-trigger--link) {
    font-size: 0.9rem;
    line-height: 1.35;
  }

  :deep(.mrt-detail-segment__meta) {
    margin-bottom: 0.4rem;
    gap: 0.35rem 0.5rem;
  }

  :deep(.mrt-detail-segment__transfer) {
    margin-top: 0.25rem;
    margin-bottom: 0.55rem;
    font-size: 0.95rem;
    line-height: 1.35;
  }

  :deep(.mrt-detail-segment__title) {
    font-size: 1rem;
  }
}

@media (max-width: 48rem) {
  .mrt-detail-panel {
    padding: 0.85rem 0.5rem 0.75rem;
    overflow-x: hidden;
  }

  :deep(.mrt-detail-segment__meta .mrt-vehicle-row) {
    justify-content: flex-start;
    width: 100%;
  }
}
</style>
