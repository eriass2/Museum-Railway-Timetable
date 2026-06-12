<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import type { TimetableBranchGroup, TimetableOverviewIconUrls } from '../../types/timetableOverview';
import { useOverviewBranchGroup } from '../../composables/useOverviewBranchGroup';
import MrtOverviewBranchTripCard from './MrtOverviewBranchTripCard.vue';
import MrtOverviewBranchTripRow from './MrtOverviewBranchTripRow.vue';

const props = defineProps<{
  group: TimetableBranchGroup;
  iconUrls: TimetableOverviewIconUrls;
  labels: OverviewUiLabels;
}>();

const { busIconUrl, departuresAria, trainLabel, tripCancelled, tripNoticeDetail, cardTripLabel } =
  useOverviewBranchGroup(() => props.group, () => props.iconUrls, () => props.labels);
</script>

<template>
  <section class="mrt-ov-group mrt-ov-group--branch">
    <header class="mrt-ov-route-header">
      <h3 class="mrt-ov-route-title">{{ group.routeLabel }}</h3>
      <p class="mrt-ov-branch-note">{{ labels.branchNote }}</p>
      <p class="mrt-ov-route-ends">
        <span>{{ group.fromLabel }}</span>
        <span class="mrt-ov-route-arrow" aria-hidden="true">→</span>
        <span>{{ group.toLabel }}</span>
      </p>
    </header>
    <div class="mrt-ov-branch-scroll">
      <table class="mrt-ov-branch-table">
        <thead>
          <tr>
            <th>{{ labels.colTrip }}</th>
            <th>{{ group.fromLabel }}</th>
            <th v-if="group.midLabel">{{ group.midLabel }}</th>
            <th>{{ group.toLabel }}</th>
            <th>{{ labels.colConnectingTrain }}</th>
          </tr>
        </thead>
        <tbody>
          <MrtOverviewBranchTripRow
            v-for="trip in group.trips"
            :key="trip.trip"
            :trip="trip"
            :bus-icon-url="busIconUrl"
            :cancelled-label="labels.cancelledLabel"
            :cancelled="tripCancelled(trip)"
            :notice-detail="tripNoticeDetail(trip)"
            :show-mid-column="!!group.midLabel"
            :train-label="trainLabel"
          />
        </tbody>
      </table>
    </div>

    <ol class="mrt-ov-branch-cards" :aria-label="departuresAria()">
      <MrtOverviewBranchTripCard
        v-for="trip in group.trips"
        :key="`card-${trip.trip}`"
        :trip="trip"
        :group="group"
        :labels="labels"
        :bus-icon-url="busIconUrl"
        :cancelled="tripCancelled(trip)"
        :card-trip-label="cardTripLabel"
        :train-label="trainLabel"
      />
    </ol>
  </section>
</template>

<style scoped>
@import './overviewStatus.css';

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

.mrt-ov-route-arrow {
  margin: 0 0.35rem;
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

.mrt-ov-branch-scroll {
  overflow-x: visible;
  padding: 0 var(--mrt-spacing-md, 1rem) var(--mrt-spacing-md, 1rem);
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

.mrt-ov-branch-cards {
  display: none;
  margin: 0;
  padding: 0 var(--mrt-spacing-md, 1rem) var(--mrt-spacing-md, 1rem);
  list-style: none;
}

@media (max-width: 40rem) {
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

  .mrt-ov-branch-scroll {
    display: none;
  }

  .mrt-ov-branch-cards {
    display: block;
  }
}
</style>
