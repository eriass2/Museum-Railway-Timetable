<script setup lang="ts">
import { computed } from 'vue';
import { overviewUiLabels } from '../../shared/overviewUiLabels';
import MrtTimetableOverviewView from '../../components/overview/MrtTimetableOverviewView.vue';
import AdminLoadState from '../components/AdminLoadState.vue';
import TimetableEditorMetaPanel from '../components/TimetableEditorMetaPanel.vue';
import TimetableEditorStoptimesPanel from '../components/TimetableEditorStoptimesPanel.vue';
import TimetableEditorDatesTab from '../components/timetable-editor/TimetableEditorDatesTab.vue';
import TimetableEditorDeviationsTab from '../components/timetable-editor/TimetableEditorDeviationsTab.vue';
import TimetableEditorTripEditForm from '../components/timetable-editor/TimetableEditorTripEditForm.vue';
import TimetableEditorTripsTab from '../components/timetable-editor/TimetableEditorTripsTab.vue';
import MobileTimetablePanel from '../components/MobileTimetablePanel.vue';
import { AdminPanel, AdminStatusMessage } from '../components/ui';
import { useTimetableEditorPage } from '../composables/useTimetableEditorPage';
import { useMobileAdmin } from '../composables/useMobileAdmin';
import { adminStr } from '../utils/adminLabels';

const props = defineProps<{ id: string }>();
const { isMobile } = useMobileAdmin();
const timetableId = computed(() => Number(props.id));
const overviewLabels = overviewUiLabels({});

const {
  cfg,
  tab,
  detail,
  overview,
  loading,
  error,
  dateInput,
  newTrip,
  destinations,
  editTrip,
  editDestinations,
  selectedServiceId,
  gridOverviewLoading,
  deviationRows,
  saveMsg,
  editTitle,
  editType,
  metaDirty,
  datesDirty,
  deviationsDirty,
  tabLabel,
  timetableTypes,
  trafficToday,
  trainTypeIconKey,
  loadDetail,
  loadOverview,
  onStoptimesGridToggle,
  loadEditDestinations,
  startEditTrip,
  cancelEditTrip,
  saveEditTrip,
  saveDates,
  saveMeta,
  removeTimetable,
  addDate,
  removeDate,
  addTrip,
  removeTrip,
  saveDeviationChanges,
  openStoptimes,
  onMobileSaved,
} = useTimetableEditorPage(() => timetableId.value);

const desktopTabs = computed(() => {
  if (isMobile.value) return [];
  return [
    ['dates', adminStr(cfg, 'editorTabDates')],
    ['trips', adminStr(cfg, 'editorTabTrips')],
    ['stoptimes', adminStr(cfg, 'editorTabStoptimes')],
    ['deviations', adminStr(cfg, 'editorTabDeviations')],
    ['preview', adminStr(cfg, 'editorTabPreview')],
  ] as const;
});
</script>

<template>
  <div>
    <h1 v-if="!detail">{{ adminStr(cfg, 'editorTitle') }}</h1>
    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'editorLoading')"
      @retry="loadDetail"
    >
      <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />

      <TimetableEditorMetaPanel
        v-if="detail && cfg.canManage"
        v-model:edit-title="editTitle"
        v-model:edit-type="editType"
        :meta-dirty="metaDirty"
        :timetable-types="timetableTypes"
        @save="saveMeta"
        @remove="removeTimetable"
      />
      <h1 v-else-if="detail">{{ detail.title }}</h1>

      <TimetableEditorDatesTab
        v-if="detail && isMobile && cfg.canManage"
        v-model:date-input="dateInput"
        :can-manage="cfg.canManage"
        :dates-dirty="datesDirty"
        :dates="detail.dates"
        @add="addDate"
        @remove="removeDate"
        @save="saveDates"
      />

      <MobileTimetablePanel
        v-if="detail && isMobile"
        :timetable-id="timetableId"
        :detail="detail"
        :can-operate="cfg.canOperate"
        :traffic-today="trafficToday"
        @saved="onMobileSaved"
      />

      <nav v-if="detail && desktopTabs.length" class="nav-tab-wrapper">
        <a
          v-for="t in desktopTabs"
          :key="t[0]"
          href="#"
          class="nav-tab"
          :class="{ 'nav-tab-active': tab === t[0] }"
          @click.prevent="tab = t[0]"
        >
          {{ tabLabel(t[1], t[0]) }}
        </a>
      </nav>

      <TimetableEditorDatesTab
        v-if="detail && !isMobile && tab === 'dates'"
        v-model:date-input="dateInput"
        :can-manage="cfg.canManage"
        :dates-dirty="datesDirty"
        :dates="detail.dates"
        @add="addDate"
        @remove="removeDate"
        @save="saveDates"
      />

      <TimetableEditorTripsTab
        v-if="detail && !isMobile && tab === 'trips'"
        v-model:new-trip="newTrip"
        :can-manage="cfg.canManage"
        :detail="detail"
        :destinations="destinations"
        :editing-trip-id="editTrip?.service_id ?? 0"
        :train-type-icon-key="trainTypeIconKey"
        @open-stoptimes="openStoptimes"
        @start-edit="startEditTrip"
        @remove-trip="removeTrip"
        @add-trip="addTrip"
      />

      <TimetableEditorTripEditForm
        v-if="detail && !isMobile && tab === 'trips' && editTrip"
        v-model:draft="editTrip"
        :detail="detail"
        :destinations="editDestinations"
        :train-type-icon-key="trainTypeIconKey"
        @route-change="loadEditDestinations(editTrip.route_id, true)"
        @save="saveEditTrip"
        @cancel="cancelEditTrip"
      />

      <TimetableEditorStoptimesPanel
        v-if="detail && !isMobile && tab === 'stoptimes'"
        v-model:selected-service-id="selectedServiceId"
        :detail="detail"
        :overview="overview"
        :grid-overview-loading="gridOverviewLoading"
        :can-manage="cfg.canManage"
        :can-operate="cfg.canOperate"
        @grid-toggle="onStoptimesGridToggle"
        @refresh-overview="loadOverview"
      />

      <TimetableEditorDeviationsTab
        v-if="detail && !isMobile && tab === 'deviations'"
        v-model:rows="deviationRows"
        :can-operate="cfg.canOperate"
        :deviations-dirty="deviationsDirty"
        :services="detail.services"
        :dates="detail.dates"
        :train-types="detail.train_types"
        :train-type-icon-key="trainTypeIconKey"
        @save="saveDeviationChanges"
      />

      <AdminPanel v-if="!isMobile && tab === 'preview'" class="mrt-vue-root">
        <MrtTimetableOverviewView v-if="overview" :data="overview" :labels="overviewLabels" />
      </AdminPanel>
    </AdminLoadState>
  </div>
</template>
