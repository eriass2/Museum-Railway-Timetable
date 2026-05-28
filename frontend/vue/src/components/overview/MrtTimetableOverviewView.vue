<script setup lang="ts">
import type {
  TimetableOverviewGroup,
  TimetableOverviewIconUrls,
  TimetableOverviewPayload,
  TimetableOverviewRow,
  TimetableVehicleCell,
} from '../../types/timetableOverview';

defineProps<{
  data: TimetableOverviewPayload;
}>();

function iconUrl(iconUrls: TimetableOverviewIconUrls, key: string): string {
  return iconUrls[key] ?? iconUrls.diesel ?? '';
}

function isTimeRow(row: TimetableOverviewRow): row is Extract<TimetableOverviewRow, { cells: { text: string }[] }> {
  return row.kind !== 'trainChange' && row.kind !== 'busConnection';
}

function isTransferRow(row: TimetableOverviewRow): row is Extract<
  TimetableOverviewRow,
  { kind: 'trainChange' | 'busConnection' }
> {
  return row.kind === 'trainChange' || row.kind === 'busConnection';
}

function rowClass(row: TimetableOverviewRow): string {
  if (row.kind === 'from' || row.kind === 'departure') return 'mrt-ov-grid-row--from';
  if (row.kind === 'to' || row.kind === 'arrival') return 'mrt-ov-grid-row--to';
  if (row.kind === 'trainChange' || row.kind === 'busConnection') return 'mrt-ov-grid-row--transfer';
  return '';
}

</script>

<template>
  <div class="mrt-ov" role="region" :aria-label="data.title">
    <p v-if="data.scope !== 'day' && data.typeBanner?.label" class="mrt-ov-banner">
      {{ data.typeBanner.label }}
    </p>
    <h2 v-else-if="data.scope === 'day'" class="mrt-ov-day-title">{{ data.title }}</h2>

    <template v-for="(group, gi) in data.groups" :key="gi">
      <section v-if="group.kind === 'rail'" class="mrt-ov-group">
        <header class="mrt-ov-route-header">
          <h3 class="mrt-ov-route-title">{{ group.routeLabel }}</h3>
          <p class="mrt-ov-route-ends">
            <span>{{ group.fromLabel }}</span>
            <span class="mrt-ov-route-arrow" aria-hidden="true">→</span>
            <span>{{ group.toLabel }}</span>
          </p>
        </header>

        <div class="mrt-ov-grid" :style="{ '--mrt-ov-cols': group.columns.length }">
          <div class="mrt-ov-grid-row mrt-ov-grid-row--head">
            <div class="mrt-ov-station-col">Station</div>
            <div
              v-for="col in group.columns"
              :key="`type-${col.serviceNumber}`"
              class="mrt-ov-col-head"
            >
              <img
                v-if="iconUrl(data.iconUrls, col.iconKey)"
                class="mrt-ov-icon"
                :src="iconUrl(data.iconUrls, col.iconKey)"
                :alt="col.trainTypeName"
                width="36"
                height="36"
              />
              <span>{{ col.trainTypeName }}</span>
            </div>
          </div>
          <div class="mrt-ov-grid-row mrt-ov-grid-row--head">
            <div class="mrt-ov-station-col mrt-ov-station-col--empty" aria-hidden="true" />
            <div
              v-for="col in group.columns"
              :key="`num-${col.serviceNumber}`"
              class="mrt-ov-col-head mrt-ov-col-head--number"
            >
              {{ col.serviceNumber }}
              <span v-if="col.specialName" class="mrt-ov-special">{{ col.specialName }}</span>
            </div>
          </div>

          <div
            v-for="(row, ri) in group.rows"
            :key="ri"
            class="mrt-ov-grid-row"
            :class="rowClass(row)"
          >
            <div class="mrt-ov-station-col">{{ row.label }}</div>
            <template v-if="isTimeRow(row)">
              <div v-for="(cell, ci) in row.cells" :key="ci" class="mrt-ov-time-cell">
                {{ cell.text }}
                <span v-if="cell.specialName" class="mrt-ov-special">{{ cell.specialName }}</span>
              </div>
            </template>
            <template v-else-if="isTransferRow(row)">
              <div v-for="(cell, ci) in row.cells" :key="ci" class="mrt-ov-transfer-cell">
                <div v-for="(v, vi) in cell.vehicles" :key="vi" class="mrt-ov-vehicle">
                  <img
                    v-if="iconUrl(data.iconUrls, v.iconKey)"
                    class="mrt-ov-icon"
                    :src="iconUrl(data.iconUrls, v.iconKey)"
                    :alt="v.typeName"
                    width="32"
                    height="32"
                  />
                  <span class="mrt-ov-vehicle-type">{{ v.typeName }}</span>
                  <span class="mrt-ov-vehicle-num">{{ v.serviceNumber }}</span>
                  <span v-if="v.detail" class="mrt-ov-vehicle-detail">{{ v.detail }}</span>
                </div>
              </div>
            </template>
          </div>
        </div>
      </section>

      <section v-else class="mrt-ov-group mrt-ov-group--branch">
        <header class="mrt-ov-route-header">
          <h3 class="mrt-ov-route-title">{{ group.routeLabel }}</h3>
          <p class="mrt-ov-branch-note">Anslutningsbuss</p>
          <p class="mrt-ov-route-ends">
            <span>{{ group.fromLabel }}</span>
            <span class="mrt-ov-route-arrow" aria-hidden="true">→</span>
            <span>{{ group.toLabel }}</span>
          </p>
        </header>
        <table class="mrt-ov-branch-table">
          <thead>
            <tr>
              <th>Tur</th>
              <th>{{ group.fromLabel }}</th>
              <th>{{ group.toLabel }}</th>
              <th>Anslutande tåg</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="trip in group.trips" :key="trip.trip">
              <th scope="row">{{ trip.trip }}</th>
              <td>{{ trip.fromTime }}</td>
              <td>{{ trip.toTime }}</td>
              <td>
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
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <div v-if="gi < data.groups.length - 1" class="mrt-ov-separator" aria-hidden="true" />
    </template>

    <section class="mrt-ov-print-key" aria-label="Förklaringar">
      <h4 class="mrt-ov-print-key-title">Förklaringar</h4>
      <table class="mrt-ov-print-key-table">
        <thead>
          <tr>
            <th>Tecken</th>
            <th>Betydelse</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, i) in data.printKey" :key="i">
            <td>{{ row.symbol }}</td>
            <td>{{ row.text }}</td>
          </tr>
        </tbody>
      </table>
      <p class="mrt-ov-print-key-note">Med reservation för ändring av tågtyp.</p>
    </section>
  </div>
</template>

<style scoped>
.mrt-ov {
  --mrt-ov-green: #2d6a4f;
  --mrt-ov-highlight: #e8f4fc;
  --mrt-ov-transfer: #fff9c4;
  --mrt-ov-transfer-label: #fff59d;
  font-size: 0.95rem;
}

.mrt-ov-banner {
  margin: 0 0 1rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, var(--mrt-ov-green), #1b4332);
  color: #fff;
  font-weight: 700;
  text-align: center;
}

.mrt-ov-day-title {
  margin: 0 0 1rem;
  font-size: 1.25rem;
  font-weight: 700;
}

.mrt-ov-group {
  margin-bottom: 1.5rem;
}

.mrt-ov-route-header {
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, var(--mrt-ov-green), #1b4332);
  color: #fff;
}

.mrt-ov-route-title {
  margin: 0 0 0.25rem;
  font-size: 1.1rem;
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

.mrt-ov-grid {
  overflow-x: auto;
  background: #fff;
}

.mrt-ov-grid-row {
  display: grid;
  grid-template-columns: minmax(7rem, 10rem) repeat(var(--mrt-ov-cols, 4), minmax(4.5rem, 1fr));
}

.mrt-ov-station-col {
  padding: 0.35rem 0.5rem;
  border: 1px solid #ddd;
  font-weight: 600;
}

.mrt-ov-station-col--empty {
  visibility: hidden;
  min-height: 0;
  padding-top: 0;
  padding-bottom: 0;
  border-color: transparent;
}

.mrt-ov-col-head {
  padding: 0.35rem 0.25rem;
  border: 1px solid #ddd;
  text-align: center;
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
}

.mrt-ov-icon {
  width: 2rem;
  height: auto;
  object-fit: contain;
}

.mrt-ov-grid-row--from .mrt-ov-station-col,
.mrt-ov-grid-row--from .mrt-ov-time-cell,
.mrt-ov-grid-row--to .mrt-ov-station-col,
.mrt-ov-grid-row--to .mrt-ov-time-cell {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-grid-row--transfer .mrt-ov-station-col,
.mrt-ov-grid-row--transfer .mrt-ov-transfer-cell {
  background: var(--mrt-ov-transfer);
}

.mrt-ov-grid-row--transfer .mrt-ov-station-col {
  background: var(--mrt-ov-transfer-label);
}

.mrt-ov-time-cell,
.mrt-ov-transfer-cell {
  padding: 0.35rem 0.25rem;
  border: 1px solid #ddd;
  text-align: center;
  font-variant-numeric: tabular-nums;
}

.mrt-ov-transfer-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.8rem;
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

.mrt-ov-vehicle-type,
.mrt-ov-vehicle-num {
  font-weight: 700;
}

.mrt-ov-special {
  display: block;
  font-size: 0.75rem;
}

.mrt-ov-branch-table {
  width: 100%;
  max-width: 40rem;
  margin: 0 1rem 1rem;
  border-collapse: collapse;
}

.mrt-ov-branch-table th,
.mrt-ov-branch-table td {
  border: 1px solid #ddd;
  padding: 0.4rem 0.6rem;
  text-align: left;
}

.mrt-ov-branch-table thead th {
  background: var(--mrt-ov-highlight);
}

.mrt-ov-branch-train {
  display: block;
}

.mrt-ov-separator {
  height: 1px;
  margin: 1.5rem 0;
  background: #ccc;
}

.mrt-ov-print-key {
  margin-top: 1.5rem;
  padding: 1rem;
}

.mrt-ov-print-key-table {
  width: 100%;
  border-collapse: collapse;
}

.mrt-ov-print-key-table th,
.mrt-ov-print-key-table td {
  border: 1px solid #ddd;
  padding: 0.35rem 0.5rem;
}
</style>
