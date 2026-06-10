<script setup lang="ts">
import type { TimetableOverviewIconUrls, TimetableRailGroup } from '../../../types/timetableOverview';
import type { OverviewGridEdit } from '../../composables/timetable-editor/useOverviewGridEdit';
import { overviewUiLabels } from '../../../shared/overviewUiLabels';
import { overviewColumnIsCancelled } from '../../../shared/overviewCancelled';
import MrtOverviewRailGroupGrid from '../../../components/overview/MrtOverviewRailGroupGrid.vue';
import { trainTypeIconUrl } from '../../../utils/overviewGrid';
import { ROAD_BUS_TRAIN_TYPE_SLUG } from '../../../shared/trainTypeIcons';
import OverviewGridCellEditor from './OverviewGridCellEditor.vue';
import MrtOverviewTimeDisplay from '../../../components/overview/MrtOverviewTimeDisplay.vue';

const labels = overviewUiLabels({});

defineProps<{
  group: TimetableRailGroup;
  iconUrls: TimetableOverviewIconUrls;
  editor: OverviewGridEdit;
  readonly?: boolean;
}>();

const emit = defineEmits<{ saved: [] }>();
</script>

<template>
  <MrtOverviewRailGroupGrid
    :group="group"
    :icon-urls="iconUrls"
    :labels="labels"
    :show-deviation-meta="true"
    :editable-cells="!readonly"
  >
    <template #time-cell="{ row, columnIndex }">
      <template v-if="readonly || !row.stationId || !group.columns[columnIndex].serviceId">
        <MrtOverviewTimeDisplay
          :text="row.cells[columnIndex].text"
          :approximate-time="row.cells[columnIndex].approximateTime"
          :cancelled="overviewColumnIsCancelled(group.columns[columnIndex])"
        />
        <span v-if="row.cells[columnIndex].busServiceNumber" class="mrt-ov-bus-ref">
          <img
            v-if="trainTypeIconUrl(iconUrls, ROAD_BUS_TRAIN_TYPE_SLUG)"
            class="mrt-ov-bus-ref__icon"
            :src="trainTypeIconUrl(iconUrls, ROAD_BUS_TRAIN_TYPE_SLUG)"
            alt=""
            width="20"
            height="20"
          />
        </span>
      </template>
      <OverviewGridCellEditor
        v-else
        :display-text="row.cells[columnIndex].text"
        :station-label="row.label"
        :service-number="group.columns[columnIndex].serviceNumber"
        :row-kind="row.kind"
        :service-id="group.columns[columnIndex].serviceId!"
        :station-id="row.stationId!"
        :edit="row.cells[columnIndex].edit"
        :editor="editor"
        @saved="emit('saved')"
      />
    </template>
    <template #transfer-cell="{ row, columnIndex }">
      <span v-if="!row.cells[columnIndex].vehicles.length">—</span>
    </template>
  </MrtOverviewRailGroupGrid>
</template>
