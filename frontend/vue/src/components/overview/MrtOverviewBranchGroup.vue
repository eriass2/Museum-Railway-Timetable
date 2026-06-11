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
