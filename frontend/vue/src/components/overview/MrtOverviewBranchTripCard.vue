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
