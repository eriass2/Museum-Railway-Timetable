<script setup lang="ts">
import { timetableTypeOverviewClass } from '../../shared/calendarDay';
import type { TimetableOverviewPayload } from '../../types/timetableOverview';

withDefaults(
  defineProps<{
    data: TimetableOverviewPayload;
    showDayTitle?: boolean;
  }>(),
  { showDayTitle: true },
);
</script>

<template>
  <div
    class="mrt-ov"
    :class="timetableTypeOverviewClass(data.timetableType)"
    role="region"
    :aria-label="data.title"
  >
    <slot name="prepend" />
    <p v-if="data.typeBanner?.label" class="mrt-ov-banner">
      {{ data.typeBanner.label }}
    </p>
    <h2 v-if="showDayTitle && data.scope === 'day'" class="mrt-ov-day-title">{{ data.title }}</h2>
    <template v-for="(group, gi) in data.groups" :key="gi">
      <slot
        name="group"
        :group="group"
        :index="gi"
        :groups="data.groups"
        :icon-urls="data.iconUrls"
      />
      <div v-if="gi < data.groups.length - 1" class="mrt-ov-separator" aria-hidden="true" />
    </template>
    <slot name="footer" :print-key="data.printKey" />
  </div>
</template>

<style>
.mrt-ov {
  --mrt-ov-header-bg: var(--mrt-color-traffic-green);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
  --mrt-ov-highlight: var(--mrt-from-to-bg, var(--mrt-light-blue-bg, #e8f4fc));
  --mrt-ov-highlight-strong: var(--mrt-from-to-col-bg, #d4ebfa);
  --mrt-ov-bus-bg: var(--mrt-bus-bg, #e3f2fd);
  --mrt-ov-bus-label: var(--mrt-from-to-col-bg, #90caf9);
  --mrt-ov-transfer: var(--mrt-transfer-bg, var(--mrt-special-bg, #fff9c4));
  --mrt-ov-transfer-label: var(--mrt-transfer-col-bg, #fff59d);
  --mrt-ov-stripe: var(--mrt-bg-lightest, #f7fbfd);
  width: 100%;
  max-width: 100%;
  font-size: var(--mrt-font-base, 0.95rem);
  line-height: 1.35;
}

.mrt-ov--green {
  --mrt-ov-header-bg: var(--mrt-color-traffic-green);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
}

.mrt-ov--yellow {
  --mrt-ov-header-bg: var(--mrt-color-traffic-yellow);
  --mrt-ov-header-fg: var(--mrt-color-on-accent);
}

.mrt-ov--red {
  --mrt-ov-header-bg: var(--mrt-color-traffic-red);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
}

.mrt-ov--orange {
  --mrt-ov-header-bg: var(--mrt-color-traffic-orange);
  --mrt-ov-header-fg: var(--mrt-color-on-green);
}

.mrt-ov--blue {
  --mrt-ov-header-bg: var(--mrt-blue-primary, #1e5a96);
  --mrt-ov-header-fg: var(--mrt-color-on-dark);
}

.mrt-ov-banner {
  margin: 0 0 var(--mrt-spacing-md, 1rem);
  padding: 0.65rem var(--mrt-spacing-md, 1rem);
  background: var(--mrt-ov-header-bg);
  color: var(--mrt-ov-header-fg);
  font-weight: 700;
  letter-spacing: 0.02em;
  text-align: center;
  text-transform: uppercase;
}

.mrt-ov-day-title {
  margin: 0 0 1rem;
  font-size: 1.25rem;
  font-weight: 700;
}

.mrt-ov-group {
  margin-bottom: var(--mrt-spacing-xl, 2rem);
  border: 1px solid var(--mrt-border-default, #d8d8d8);
  box-shadow: var(--mrt-shadow-md, 0 2px 10px rgba(0, 0, 0, 0.06));
}

.mrt-ov-route-header {
  padding: 0.85rem var(--mrt-spacing-md, 1rem);
  background: var(--mrt-ov-header-bg);
  color: var(--mrt-ov-header-fg);
}

.mrt-ov-route-title {
  margin: 0 0 0.25rem;
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.2;
}

.mrt-ov-route-ends,
.mrt-ov-branch-note {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.95;
}

.mrt-ov-branch-note {
  font-weight: 600;
}

.mrt-ov-route-arrow {
  margin: 0 0.35rem;
}

.mrt-ov-grid-scroll {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.mrt-ov-grid {
  --mrt-ov-station-w: 10.5rem;
  --mrt-ov-col-min: 3.35rem;
  --mrt-ov-col-max: 4.1rem;
  --mrt-ov-highlight-w: 1.15rem;
  --mrt-ov-num-size: 1rem;
  --mrt-ov-footnote-size: calc(var(--mrt-ov-num-size) * 0.5);
  --mrt-ov-text-size: var(--mrt-ov-footnote-size);
  display: grid;
  width: max(100%, var(--mrt-ov-grid-min, 30rem));
  background: #fff;
}

.mrt-ov-grid-row {
  display: contents;
}

.mrt-ov-grid-row--head .mrt-ov-station-col,
.mrt-ov-grid-row--head .mrt-ov-col-head {
  background: var(--mrt-ov-highlight-strong);
  font-weight: 700;
}

.mrt-ov-grid-row--head + .mrt-ov-grid-row--head .mrt-ov-col-head {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-grid-row--head + .mrt-ov-grid-row--head .mrt-ov-station-col {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-col-head--number {
  font-size: var(--mrt-ov-num-size);
  font-weight: 700;
}

.mrt-ov-station-col {
  position: sticky;
  left: 0;
  z-index: 2;
  padding: var(--mrt-cell-padding-md, 0.4rem 0.55rem);
  border: 1px solid var(--mrt-border-default, #ccc);
  font-size: var(--mrt-ov-num-size);
  font-weight: 400;
  background: #fff;
  border-right-width: 2px;
}

.mrt-ov-grid-row--head .mrt-ov-station-col {
  z-index: 3;
  font-weight: 600;
}

.mrt-ov-col-head {
  padding: 0.4rem 0.25rem;
  border: 1px solid var(--mrt-border-default, #ccc);
  text-align: center;
  font-size: var(--mrt-ov-num-size);
  font-weight: 600;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  min-width: 0;
}

.mrt-ov-col-head--type {
  padding: 0.28rem 0.12rem;
  gap: 0.12rem;
  font-size: calc(var(--mrt-ov-num-size) * 0.72);
  line-height: 1.15;
}

.mrt-ov-col-head__type-name {
  max-width: 100%;
  overflow-wrap: anywhere;
}

.mrt-ov-icon {
  width: 2.1rem;
  height: auto;
  object-fit: contain;
}

.mrt-ov-icon--head {
  width: 1.45rem;
  max-height: 1.45rem;
}

.mrt-ov-grid-row--from .mrt-ov-station-col,
.mrt-ov-grid-row--from .mrt-ov-time-cell,
.mrt-ov-grid-row--to .mrt-ov-station-col,
.mrt-ov-grid-row--to .mrt-ov-time-cell {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-grid-row--alt .mrt-ov-station-col,
.mrt-ov-grid-row--alt .mrt-ov-time-cell {
  background: var(--mrt-ov-stripe);
}

.mrt-ov-highlight-stripe {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-block: 0.35rem;
  padding-inline: 1px;
  border: 1px solid var(--mrt-border-default, #ccc);
  background: var(--mrt-ov-cell-highlight, var(--mrt-special-bg, #fff9c4));
  min-height: 2.5rem;
}

.mrt-ov-highlight-stripe--head {
  min-height: 0;
  padding-block: 0.25rem;
  padding-inline: 1px;
}

.mrt-ov-highlight-stripe--span {
  align-self: stretch;
  min-height: 0;
  padding-block: 0.5rem;
  padding-inline: 1px;
}

.mrt-ov-highlight-stripe__label {
  display: block;
  font-size: var(--mrt-ov-footnote-size);
  font-weight: 700;
  line-height: 1.1;
  text-align: center;
  writing-mode: vertical-rl;
  text-orientation: mixed;
  transform: rotate(180deg);
}

.mrt-ov-highlight-stripe--span .mrt-ov-highlight-stripe__label {
  line-height: 1.15;
  letter-spacing: 0.01em;
}

.mrt-ov-grid-row--transfer .mrt-ov-station-col,
.mrt-ov-grid-row--transfer .mrt-ov-transfer-cell:not(.mrt-ov-transfer-cell--empty) {
  background: var(--mrt-ov-transfer);
}

.mrt-ov-grid-row--transfer-type .mrt-ov-station-col {
  background: var(--mrt-ov-transfer-label);
  font-weight: 600;
}

.mrt-ov-grid-row--transfer-number .mrt-ov-station-col {
  background: var(--mrt-ov-transfer);
  min-height: 0;
}

.mrt-ov-grid-row--bus .mrt-ov-station-col,
.mrt-ov-grid-row--bus .mrt-ov-time-cell {
  background: var(--mrt-ov-bus-bg);
}

.mrt-ov-grid-row--bus .mrt-ov-station-col {
  background: var(--mrt-ov-bus-label);
  font-weight: 400;
}

.mrt-ov-station-col--bus {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.mrt-ov-bus-station-icon {
  width: 1.2rem;
  height: auto;
  flex-shrink: 0;
  object-fit: contain;
}

.mrt-ov-time {
  display: inline-flex;
  align-items: baseline;
  flex-wrap: nowrap;
  max-width: 100%;
  font-weight: 400;
  line-height: 1;
  white-space: nowrap;
}

.mrt-ov-time__ca,
.mrt-ov-time__suffix {
  flex: 0 0 auto;
  font-size: var(--mrt-ov-footnote-size);
  font-weight: 400;
  line-height: 1;
}

.mrt-ov-time__ca {
  margin-right: 0.12em;
}

.mrt-ov-time__suffix {
  margin-left: 0.12em;
}

.mrt-ov-time__prefix {
  flex: 0 0 auto;
  font-size: var(--mrt-ov-footnote-size);
  font-weight: inherit;
}

.mrt-ov-time__value {
  flex: 0 1 auto;
  font-size: 1em;
  font-weight: inherit;
}

.mrt-ov-grid-row--transfer .mrt-ov-transfer-cell--empty {
  background: #fff;
  min-height: 0;
  padding-block: 0.25rem;
}

.mrt-ov-time-cell,
.mrt-ov-transfer-cell {
  padding: var(--mrt-cell-padding-md, 0.4rem 0.3rem);
  border: 1px solid var(--mrt-border-default, #ccc);
  text-align: center;
  font-size: var(--mrt-ov-num-size);
  font-variant-numeric: tabular-nums;
  line-height: 1;
  white-space: nowrap;
  min-width: 0;
}

.mrt-ov-transfer-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  min-height: 3.5rem;
}

.mrt-ov-transfer-cell--change-type {
  min-height: 0;
  padding: 0.28rem 0.12rem;
  gap: 0.12rem;
  font-size: calc(var(--mrt-ov-num-size) * 0.72);
  line-height: 1.15;
}

.mrt-ov-transfer-cell--change-number {
  min-height: 0;
  padding: 0.25rem 0.12rem;
  font-size: var(--mrt-ov-num-size);
  font-weight: 700;
}

.mrt-ov-transfer-num {
  font-variant-numeric: tabular-nums;
}

.mrt-ov-vehicle {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.1rem;
}

.mrt-ov-vehicle + .mrt-ov-vehicle {
  margin-top: 0.25rem;
  padding-top: 0.25rem;
  border-top: 1px dashed #bbb;
}

.mrt-ov-vehicle-type {
  font-size: var(--mrt-ov-text-size);
  font-weight: 700;
}

.mrt-ov-vehicle-num {
  font-size: var(--mrt-ov-num-size);
  font-weight: 700;
}

.mrt-ov-vehicle-detail {
  font-size: var(--mrt-ov-text-size);
  line-height: 1.25;
}

.mrt-ov-special {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
}

.mrt-ov-deviation-mark {
  margin-left: 0.15rem;
  font-weight: 700;
  text-decoration: none;
  cursor: help;
}

.mrt-ov-deviation-note {
  display: block;
  margin-top: 0.2rem;
  font-size: var(--mrt-ov-text-size);
  font-weight: 600;
  line-height: 1.25;
  color: var(--mrt-color-warning-800, #7a4f01);
}

.mrt-ov-cancelled-badge {
  display: block;
  margin-top: 0.2rem;
  font-size: var(--mrt-ov-text-size);
  font-weight: 700;
  line-height: 1.25;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--mrt-text-error, #b32d2e);
}

.mrt-ov-deviation-note--cancelled-detail {
  color: var(--mrt-text-error, #b32d2e);
}

.mrt-ov-col-head--cancelled {
  background: var(--mrt-color-neutral-100, #f3f3f3);
}

.mrt-ov-icon--cancelled {
  opacity: 0.45;
  filter: grayscale(1);
}

.mrt-ov-time-cell--cancelled {
  background: var(--mrt-color-neutral-100, #f3f3f3);
}

.mrt-ov-time--cancelled {
  text-decoration: line-through;
  color: var(--mrt-color-neutral-600, #666);
}

.mrt-ov-branch-scroll {
  overflow-x: visible;
  padding: 0 var(--mrt-spacing-md, 1rem) var(--mrt-spacing-md, 1rem);
}

.mrt-ov-branch-table tbody tr.mrt-ov-branch-row--cancelled {
  background: var(--mrt-color-neutral-100, #f3f3f3);
}

.mrt-ov-branch-card--cancelled {
  background: var(--mrt-color-neutral-100, #f3f3f3);
  opacity: 0.95;
}

.mrt-ov-branch-table th,
.mrt-ov-branch-table td {
  border: 1px solid var(--mrt-border-default, #ccc);
  padding: 0.45rem 0.65rem;
  text-align: left;
}

.mrt-ov-branch-table thead th {
  background: var(--mrt-ov-highlight-strong);
  font-weight: 700;
  white-space: normal;
}

.mrt-ov-branch-train {
  display: block;
  font-size: 0.85rem;
}

.mrt-ov-branch-trip-icon {
  display: block;
  margin: 0 auto;
}

.mrt-ov-branch-cards {
  display: none;
  margin: 0;
  padding: 0 var(--mrt-spacing-md, 1rem) var(--mrt-spacing-md, 1rem);
  list-style: none;
}

.mrt-ov-branch-card {
  margin: 0;
  padding: 0.75rem 0.85rem;
  border: 1px solid var(--mrt-border-default, #ccc);
  border-radius: var(--mrt-radius-sm, 4px);
  background: #fff;
}

.mrt-ov-branch-card + .mrt-ov-branch-card {
  margin-top: 0.65rem;
}

.mrt-ov-branch-card__trip {
  margin: 0 0 0.5rem;
  font-size: 0.95rem;
  font-weight: 700;
}

.mrt-ov-branch-card__times {
  margin: 0;
}

.mrt-ov-branch-card__row {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.35rem 0;
  border-top: 1px solid var(--mrt-border-default, #e8e8e8);
}

.mrt-ov-branch-card__row:first-child {
  border-top: 0;
  padding-top: 0;
}

.mrt-ov-branch-card__row dt {
  margin: 0;
  flex: 1 1 55%;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--mrt-color-neutral-700, #404040);
}

.mrt-ov-branch-card__row dd {
  margin: 0;
  flex: 0 0 auto;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  text-align: right;
}

.mrt-ov-separator {
  height: 1px;
  margin: 1.75rem 0;
  background: #ccc;
}

.mrt-ov-print-key {
  margin-top: var(--mrt-spacing-lg, 1.5rem);
  padding: var(--mrt-spacing-md, 1rem) 1.25rem;
  background: var(--mrt-bg-lightest, #fafafa);
  border: 1px solid var(--mrt-border-default, #ddd);
}

.mrt-ov-print-key-title {
  margin: 0 0 0.75rem;
  font-size: 1rem;
  font-weight: 700;
}

.mrt-ov-print-key-table {
  width: 100%;
  border-collapse: collapse;
}

.mrt-ov-print-key-table th,
.mrt-ov-print-key-table td {
  border: 1px solid var(--mrt-border-default, #ccc);
  padding: 0.4rem 0.55rem;
  vertical-align: top;
}

.mrt-ov-print-key-table th {
  width: 30%;
  background: var(--mrt-wizard-surface, #fff);
  font-weight: 700;
}

.mrt-ov-print-key-note {
  margin: 0.75rem 0 0;
  font-size: var(--mrt-font-sm, 0.85rem);
  color: var(--mrt-color-neutral-600, #525252);
  font-style: italic;
}

@media (max-width: 40rem) {
  .mrt-ov-banner {
    font-size: 0.9rem;
    padding-inline: 0.75rem;
  }

  .mrt-ov-day-title {
    font-size: 1.1rem;
  }

  .mrt-ov-route-header {
    padding-inline: 0.75rem;
  }

  .mrt-ov-route-title {
    font-size: 1.05rem;
  }

  .mrt-ov-route-ends,
  .mrt-ov-branch-note {
    font-size: 0.85rem;
  }

  .mrt-ov-grid {
    --mrt-ov-station-w: 8.5rem;
    --mrt-ov-col-min: 3.25rem;
    --mrt-ov-num-size: 0.9rem;
  }

  .mrt-ov-icon {
    width: 1.75rem;
  }

  .mrt-ov-icon--head {
    width: 1.3rem;
    max-height: 1.3rem;
  }

  .mrt-ov-branch-scroll {
    display: none;
  }

  .mrt-ov-branch-cards {
    display: block;
  }

  .mrt-ov-print-key {
    padding-inline: 0.85rem;
  }

  .mrt-ov-print-key-table thead {
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

  .mrt-ov-print-key-table tbody tr {
    display: block;
    margin-bottom: 0.65rem;
    border: 1px solid var(--mrt-border-default, #ccc);
    background: var(--mrt-wizard-surface, #fff);
  }

  .mrt-ov-print-key-table tbody tr:last-child {
    margin-bottom: 0;
  }

  .mrt-ov-print-key-table td {
    display: block;
    border: 0;
    padding: 0.45rem 0.65rem;
  }

  .mrt-ov-print-key-table td:first-child {
    padding-bottom: 0.25rem;
    font-weight: 700;
    background: var(--mrt-bg-lightest, #fafafa);
    border-bottom: 1px solid var(--mrt-border-default, #e8e8e8);
  }
}
</style>
