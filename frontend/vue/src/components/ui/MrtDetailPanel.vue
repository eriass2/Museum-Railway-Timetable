<template>
  <div class="mrt-detail-panel">
    <slot />
  </div>
</template>

<style scoped>
.mrt-detail-panel {
  container-type: inline-size;
  container-name: mrt-detail;
  padding: 1.2rem var(--mrt-spacing-md, 1rem) 1rem;
  background: var(--mrt-wizard-surface, #fff);
  --mrt-tl-time: clamp(3.25rem, 8vw, 5.4rem);
  --mrt-tl-node: clamp(1rem, 2.5vw, 1.6rem);
  --mrt-tl-gap: clamp(0.35rem, 1vw, 0.65rem);
  --mrt-tl-content-start: calc(var(--mrt-tl-time) + var(--mrt-tl-gap) + var(--mrt-tl-node) + var(--mrt-tl-gap));
  --mrt-detail-segment-meta-gap: 0.5rem 0.8rem;
  --mrt-detail-segment-meta-margin-bottom: 0.55rem;
  --mrt-detail-segment-transfer-margin-top: 0.45rem;
  --mrt-detail-segment-transfer-margin-bottom: 0.8rem;
  --mrt-detail-segment-transfer-font-size: 1.1rem;
  --mrt-detail-segment-title-font-size: inherit;
  --mrt-vehicle-row-justify: flex-end;
  --mrt-vehicle-row-width: auto;
}

.mrt-detail-panel--multi {
  display: grid;
  gap: 1rem;
}

@container mrt-detail (max-width: var(--mrt-max-narrow)) {
  .mrt-detail-panel {
    --mrt-tl-time: clamp(3rem, 18cqi, 3.75rem);
    --mrt-tl-node: 1.1rem;
    --mrt-tl-gap: 0.4rem;
    --mrt-detail-segment-meta-gap: 0.35rem 0.5rem;
    --mrt-detail-segment-meta-margin-bottom: 0.4rem;
    --mrt-detail-segment-transfer-margin-top: 0.25rem;
    --mrt-detail-segment-transfer-margin-bottom: 0.55rem;
    --mrt-detail-segment-transfer-font-size: 0.95rem;
    --mrt-detail-segment-title-font-size: 1rem;
  }
}

@media (max-width: 48rem) {
  .mrt-detail-panel {
    padding: 0.85rem var(--mrt-spacing-sm, 0.75rem) 0.75rem;
    overflow-x: hidden;
    --mrt-vehicle-row-justify: flex-start;
    --mrt-vehicle-row-width: 100%;
  }
}
</style>
