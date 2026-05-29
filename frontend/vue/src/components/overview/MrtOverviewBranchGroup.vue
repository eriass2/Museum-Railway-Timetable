<script setup lang="ts">
import type { TimetableBranchGroup } from '../../types/timetableOverview';

defineProps<{
  group: TimetableBranchGroup;
}>();
</script>

<template>
  <section class="mrt-ov-group mrt-ov-group--branch">
    <header class="mrt-ov-route-header">
      <h3 class="mrt-ov-route-title">{{ group.routeLabel }}</h3>
      <p class="mrt-ov-branch-note">Anslutningsbuss</p>
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
            <th>Tur</th>
            <th>{{ group.fromLabel }}</th>
            <th v-if="group.midLabel">{{ group.midLabel }}</th>
            <th>{{ group.toLabel }}</th>
            <th>Anslutande tåg</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="trip in group.trips" :key="trip.trip">
            <th scope="row">{{ trip.trip }}</th>
            <td>{{ trip.fromTime }}</td>
            <td v-if="group.midLabel">{{ trip.midTime || '—' }}</td>
            <td>{{ trip.toTime }}</td>
            <td>
              <template v-if="trip.connectingTrains.length">
                <span v-for="(t, ti) in trip.connectingTrains" :key="ti" class="mrt-ov-branch-train">
                  Tåg {{ t.serviceNumber }} {{ t.timeDisplay }}
                </span>
              </template>
              <span v-else>—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ol class="mrt-ov-branch-cards" :aria-label="`Avgångar ${group.routeLabel}`">
      <li v-for="trip in group.trips" :key="`card-${trip.trip}`" class="mrt-ov-branch-card">
        <p class="mrt-ov-branch-card__trip">Tur {{ trip.trip }}</p>
        <dl class="mrt-ov-branch-card__times">
          <div class="mrt-ov-branch-card__row">
            <dt>{{ group.fromLabel }}</dt>
            <dd>{{ trip.fromTime }}</dd>
          </div>
          <div v-if="group.midLabel" class="mrt-ov-branch-card__row">
            <dt>{{ group.midLabel }}</dt>
            <dd>{{ trip.midTime || '—' }}</dd>
          </div>
          <div class="mrt-ov-branch-card__row">
            <dt>{{ group.toLabel }}</dt>
            <dd>{{ trip.toTime }}</dd>
          </div>
          <div class="mrt-ov-branch-card__row">
            <dt>Anslutande tåg</dt>
            <dd>
              <template v-if="trip.connectingTrains.length">
                <span
                  v-for="(t, ti) in trip.connectingTrains"
                  :key="ti"
                  class="mrt-ov-branch-train"
                >
                  Tåg {{ t.serviceNumber }} {{ t.timeDisplay }}
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
