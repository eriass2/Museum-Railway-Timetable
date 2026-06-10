<script setup lang="ts">
import type { OverviewUiLabels } from '../../shared/overviewUiLabels';
import {
  formatCardTrip,
  formatDeparturesAria,
  formatTrainConnecting,
} from '../../shared/overviewUiLabels';
import type { TimetableBranchGroup, TimetableOverviewIconUrls } from '../../types/timetableOverview';
import { branchTripIsCancelled, branchTripNoticeDetail } from '../../shared/branchTripCancelled';
import { trainTypeIconUrl } from '../../utils/overviewGrid';
import { ROAD_BUS_TRAIN_TYPE_SLUG } from '../../shared/trainTypeIcons';

const props = defineProps<{
  group: TimetableBranchGroup;
  iconUrls: TimetableOverviewIconUrls;
  labels: OverviewUiLabels;
}>();

const busIconUrl = trainTypeIconUrl(props.iconUrls, ROAD_BUS_TRAIN_TYPE_SLUG);

function departuresAria(): string {
  return formatDeparturesAria(props.labels.departuresAria, props.group.routeLabel);
}

function trainLabel(serviceNumber: string, timeDisplay: string): string {
  return formatTrainConnecting(props.labels.trainConnecting, serviceNumber, timeDisplay);
}

function tripCancelled(trip: TimetableBranchGroup['trips'][number]): boolean {
  return branchTripIsCancelled(trip);
}
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
          <tr v-for="trip in group.trips" :key="trip.trip" :class="{ 'mrt-ov-branch-row--cancelled': tripCancelled(trip) }">
            <th scope="row">
              <img
                v-if="busIconUrl"
                class="mrt-ov-branch-trip-icon"
                :class="{ 'mrt-ov-icon--cancelled': tripCancelled(trip) }"
                :src="busIconUrl"
                alt=""
                width="24"
                height="24"
              />
              <span class="screen-reader-text">{{ trip.trip }}</span>
              <span v-if="tripCancelled(trip)" class="mrt-ov-cancelled-badge">
                {{ labels.cancelledLabel }}
              </span>
              <span
                v-if="branchTripNoticeDetail(trip, labels.cancelledLabel)"
                class="mrt-ov-deviation-note mrt-ov-deviation-note--cancelled-detail"
              >
                {{ branchTripNoticeDetail(trip, labels.cancelledLabel) }}
              </span>
            </th>
            <td :class="{ 'mrt-ov-time--cancelled': tripCancelled(trip) }">{{ trip.fromTime }}</td>
            <td v-if="group.midLabel" :class="{ 'mrt-ov-time--cancelled': tripCancelled(trip) }">
              {{ trip.midTime || '—' }}
            </td>
            <td :class="{ 'mrt-ov-time--cancelled': tripCancelled(trip) }">{{ trip.toTime }}</td>
            <td>
              <template v-if="trip.connectingTrains.length">
                <span v-for="(t, ti) in trip.connectingTrains" :key="ti" class="mrt-ov-branch-train">
                  {{ trainLabel(t.serviceNumber, t.timeDisplay) }}
                </span>
              </template>
              <span v-else>—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ol class="mrt-ov-branch-cards" :aria-label="departuresAria()">
      <li
        v-for="trip in group.trips"
        :key="`card-${trip.trip}`"
        class="mrt-ov-branch-card"
        :class="{ 'mrt-ov-branch-card--cancelled': tripCancelled(trip) }"
      >
        <p class="mrt-ov-branch-card__trip">
          <img
            v-if="busIconUrl"
            class="mrt-ov-branch-trip-icon"
            :class="{ 'mrt-ov-icon--cancelled': tripCancelled(trip) }"
            :src="busIconUrl"
            alt=""
            width="24"
            height="24"
          />
          <span v-if="tripCancelled(trip)" class="mrt-ov-cancelled-badge">
            {{ labels.cancelledLabel }}
          </span>
          <span class="screen-reader-text">{{ formatCardTrip(labels.cardTrip, trip.trip) }}</span>
        </p>
        <dl class="mrt-ov-branch-card__times">
          <div class="mrt-ov-branch-card__row">
            <dt>{{ group.fromLabel }}</dt>
            <dd :class="{ 'mrt-ov-time--cancelled': tripCancelled(trip) }">{{ trip.fromTime }}</dd>
          </div>
          <div v-if="group.midLabel" class="mrt-ov-branch-card__row">
            <dt>{{ group.midLabel }}</dt>
            <dd :class="{ 'mrt-ov-time--cancelled': tripCancelled(trip) }">{{ trip.midTime || '—' }}</dd>
          </div>
          <div class="mrt-ov-branch-card__row">
            <dt>{{ group.toLabel }}</dt>
            <dd :class="{ 'mrt-ov-time--cancelled': tripCancelled(trip) }">{{ trip.toTime }}</dd>
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
    </ol>
  </section>
</template>
