<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableBranchGroup, TimetableBranchTrip } from '../../types/timetableOverview';

defineProps<{
  trip: TimetableBranchTrip;
  group: TimetableBranchGroup;
  labels: OverviewUiLabels;
  busIconUrl: string;
  cancelled: boolean;
  cardTripLabel: (tripNumber: string) => string;
  trainLabel: (serviceNumber: string, timeDisplay: string) => string;
}>();
</script>

<template>
  <li
    class="mrt-ov-branch-card"
    :class="{ 'mrt-ov-branch-card--cancelled': cancelled }"
  >
    <p class="mrt-ov-branch-card__trip">
      <img
        v-if="busIconUrl"
        class="mrt-ov-branch-trip-icon"
        :class="{ 'mrt-ov-icon--cancelled': cancelled }"
        :src="busIconUrl"
        alt=""
        width="24"
        height="24"
      />
      <span v-if="cancelled" class="mrt-ov-cancelled-badge">
        {{ labels.cancelledLabel }}
      </span>
      <span class="screen-reader-text">{{ cardTripLabel(trip.trip) }}</span>
    </p>
    <dl class="mrt-ov-branch-card__times">
      <div class="mrt-ov-branch-card__row">
        <dt>{{ group.fromLabel }}</dt>
        <dd :class="{ 'mrt-ov-time--cancelled': cancelled }">{{ trip.fromTime }}</dd>
      </div>
      <div v-if="group.midLabel" class="mrt-ov-branch-card__row">
        <dt>{{ group.midLabel }}</dt>
        <dd :class="{ 'mrt-ov-time--cancelled': cancelled }">{{ trip.midTime || '—' }}</dd>
      </div>
      <div class="mrt-ov-branch-card__row">
        <dt>{{ group.toLabel }}</dt>
        <dd :class="{ 'mrt-ov-time--cancelled': cancelled }">{{ trip.toTime }}</dd>
      </div>
      <div class="mrt-ov-branch-card__row">
        <dt>{{ labels.colConnectingTrain }}</dt>
        <dd>
          <template v-if="trip.connectingTrains.length">
            <span
              v-for="(t, ti) in trip.connectingTrains"
              :key="ti"
              class="mrt-ov-branch-train"
            >
              {{ trainLabel(t.serviceNumber, t.timeDisplay) }}
            </span>
          </template>
          <span v-else>—</span>
        </dd>
      </div>
    </dl>
  </li>
</template>

<style scoped>
@import './overviewStatus.css';

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

.mrt-ov-branch-card--cancelled {
  background: var(--mrt-color-neutral-100, #f3f3f3);
  opacity: 0.95;
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

.mrt-ov-branch-trip-icon {
  display: block;
  margin: 0 auto;
}

.mrt-ov-branch-train {
  display: block;
  font-size: 0.85rem;
}
</style>
