<script setup lang="ts">
import type { TimetableBranchTrip } from '../../types/timetableOverview';

defineProps<{
  trip: TimetableBranchTrip;
  busIconUrl: string;
  cancelledLabel: string;
  cancelled: boolean;
  noticeDetail: string;
  showMidColumn: boolean;
  trainLabel: (serviceNumber: string, timeDisplay: string) => string;
}>();
</script>

<template>
  <tr :class="{ 'mrt-ov-branch-row--cancelled': cancelled }">
    <th scope="row">
      <img
        v-if="busIconUrl"
        class="mrt-ov-branch-trip-icon"
        :class="{ 'mrt-ov-icon--cancelled': cancelled }"
        :src="busIconUrl"
        alt=""
        width="24"
        height="24"
      />
      <span class="screen-reader-text">{{ trip.trip }}</span>
      <span v-if="cancelled" class="mrt-ov-cancelled-badge">
        {{ cancelledLabel }}
      </span>
      <span
        v-if="noticeDetail"
        class="mrt-ov-deviation-note mrt-ov-deviation-note--cancelled-detail"
      >
        {{ noticeDetail }}
      </span>
    </th>
    <td :class="{ 'mrt-ov-time--cancelled': cancelled }">{{ trip.fromTime }}</td>
    <td v-if="showMidColumn" :class="{ 'mrt-ov-time--cancelled': cancelled }">
      {{ trip.midTime || '—' }}
    </td>
    <td :class="{ 'mrt-ov-time--cancelled': cancelled }">{{ trip.toTime }}</td>
    <td>
      <template v-if="trip.connectingTrains.length">
        <span v-for="(t, ti) in trip.connectingTrains" :key="ti" class="mrt-ov-branch-train">
          {{ trainLabel(t.serviceNumber, t.timeDisplay) }}
        </span>
      </template>
      <span v-else>—</span>
    </td>
  </tr>
</template>

<style scoped>
@import './overviewStatus.css';

.mrt-ov-branch-row--cancelled {
  background: var(--mrt-color-neutral-100, #f3f3f3);
}

.mrt-ov-branch-train {
  display: block;
  font-size: 0.85rem;
}

.mrt-ov-branch-trip-icon {
  display: block;
  margin: 0 auto;
}
</style>
