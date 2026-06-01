<script setup lang="ts">
import type { TimetableOverviewIconUrls, TimetableRailGroup, TimetableTimeCellEdit } from '../../types/timetableOverview';
import type { OverviewGridEdit } from '../composables/useOverviewGridEdit';
import { overviewUiLabels } from '../../shared/overviewUiLabels';
import MrtOverviewRailGroupGrid from '../../components/overview/MrtOverviewRailGroupGrid.vue';
import { trainTypeIconUrl } from '../../utils/overviewGrid';

const labels = overviewUiLabels({});

const props = defineProps<{
  group: TimetableRailGroup;
  iconUrls: TimetableOverviewIconUrls;
  editor: OverviewGridEdit;
  readonly?: boolean;
}>();

function showDeparture(kind: string): boolean {
  return kind === 'from' || kind === 'departure' || kind === 'station';
}

function showArrival(kind: string): boolean {
  return kind === 'to' || kind === 'arrival' || kind === 'station';
}

async function patchCell(
  serviceId: number,
  stationId: number,
  cell: TimetableTimeCellEdit | undefined,
  patch: Partial<TimetableTimeCellEdit>,
) {
  const merged = props.editor.mergeEdit(serviceId, stationId, cell, patch);
  await props.editor.applyCellEdit(serviceId, stationId, merged);
}
</script>

<template>
  <MrtOverviewRailGroupGrid
    :group="group"
    :icon-urls="iconUrls"
    :labels="labels"
    :show-deviation-meta="false"
    :editable-cells="!readonly"
  >
    <template #time-cell="{ row, track, columnIndex }">
      <template v-if="readonly || !row.stationId || !group.columns[columnIndex].serviceId">
        <span class="mrt-ov-time">{{ row.cells[columnIndex].text }}</span>
        <span v-if="row.cells[columnIndex].busServiceNumber" class="mrt-ov-bus-ref">
          <img
            v-if="trainTypeIconUrl(iconUrls, 'bus')"
            class="mrt-ov-bus-ref__icon"
            :src="trainTypeIconUrl(iconUrls, 'bus')"
            alt=""
            width="20"
            height="20"
          />
        </span>
      </template>
      <template v-else>
        <label class="mrt-ov-edit-stop">
          <input
            type="checkbox"
            :checked="row.cells[columnIndex].edit?.stopsHere ?? false"
            @change="patchCell(
              group.columns[columnIndex].serviceId!,
              row.stationId!,
              row.cells[columnIndex].edit,
              { stopsHere: ($event.target as HTMLInputElement).checked },
            )"
          />
          Stannar
        </label>
        <input
          v-if="showDeparture(row.kind)"
          type="time"
          class="mrt-ov-edit-time"
          :value="editor.hhmmToInput(row.cells[columnIndex].edit?.departure ?? '')"
          @change="patchCell(
            group.columns[columnIndex].serviceId!,
            row.stationId!,
            row.cells[columnIndex].edit,
            { departure: editor.inputToHhmm(($event.target as HTMLInputElement).value), stopsHere: true },
          )"
        />
        <input
          v-if="showArrival(row.kind)"
          type="time"
          class="mrt-ov-edit-time"
          :value="editor.hhmmToInput(row.cells[columnIndex].edit?.arrival ?? '')"
          @change="patchCell(
            group.columns[columnIndex].serviceId!,
            row.stationId!,
            row.cells[columnIndex].edit,
            { arrival: editor.inputToHhmm(($event.target as HTMLInputElement).value), stopsHere: true },
          )"
        />
        <span class="mrt-ov-edit-pa">
          <label>
            <input
              type="checkbox"
              :checked="row.cells[columnIndex].edit?.pickupAllowed ?? true"
              @change="patchCell(
                group.columns[columnIndex].serviceId!,
                row.stationId!,
                row.cells[columnIndex].edit,
                { pickupAllowed: ($event.target as HTMLInputElement).checked },
              )"
            />
            P
          </label>
          <label>
            <input
              type="checkbox"
              :checked="row.cells[columnIndex].edit?.dropoffAllowed ?? true"
              @change="patchCell(
                group.columns[columnIndex].serviceId!,
                row.stationId!,
                row.cells[columnIndex].edit,
                { dropoffAllowed: ($event.target as HTMLInputElement).checked },
              )"
            />
            A
          </label>
        </span>
      </template>
    </template>
    <template #transfer-cell="{ row, columnIndex }">
      <span v-if="!row.cells[columnIndex].vehicles.length">—</span>
    </template>
  </MrtOverviewRailGroupGrid>
</template>

<style scoped>
.mrt-ov-edit-time {
  width: 100%;
  max-width: 6rem;
  font-size: 0.75rem;
}
.mrt-ov-edit-pa {
  display: flex;
  gap: 0.35rem;
  justify-content: center;
}
.mrt-ov-edit-stop {
  display: flex;
  align-items: center;
  gap: 0.2rem;
  justify-content: center;
}
</style>
