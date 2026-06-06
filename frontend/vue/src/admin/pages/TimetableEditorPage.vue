<script setup lang="ts">
import { computed } from 'vue';
import { overviewUiLabels } from '../../shared/overviewUiLabels';
import MrtTimetableOverviewView from '../../components/overview/MrtTimetableOverviewView.vue';
import AdminLoadState from '../components/AdminLoadState.vue';
import TimetableEditorMetaPanel from '../components/TimetableEditorMetaPanel.vue';
import TimetableEditorStoptimesPanel from '../components/TimetableEditorStoptimesPanel.vue';
import TimetableEditorDatesTab from '../components/timetable-editor/TimetableEditorDatesTab.vue';
import TimetableEditorDeviationsTab from '../components/timetable-editor/TimetableEditorDeviationsTab.vue';
import TimetableEditorTripsTab from '../components/timetable-editor/TimetableEditorTripsTab.vue';
import MobileTimetablePanel from '../components/MobileTimetablePanel.vue';
import { AdminPanel, AdminStatusMessage } from '../components/ui';
import { useTimetableEditorPage } from '../composables/useTimetableEditorPage';
import type { TimetableEditorTab } from '../composables/useTimetableEditorPage';
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
  tripsView,
  stoptimesView,
  stoptimesPanelRef,
  deviationsTabRef,
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
  startCreateTrip,
  requestBackToTripsList,
  requestBackToStoptimesList,
  switchTab,
  backToStoptimesList,
  startEditTrip,
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

function openStoptimesDetail(serviceId: number): void {
  selectedServiceId.value = serviceId;
  stoptimesView.value = 'detail';
}

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

function onTabClick(next: TimetableEditorTab): void {
  void switchTab(next);
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
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
          @click.prevent="onTabClick(t[0])"
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
        v-model:edit-trip="editTrip"
        :can-manage="cfg.canManage"
        :detail="detail"
        :destinations="destinations"
        :edit-destinations="editDestinations"
        :view-mode="tripsView"
        :train-type-icon-key="trainTypeIconKey"
        @open-stoptimes="openStoptimes"
        @start-create="startCreateTrip"
        @start-edit="startEditTrip"
        @back="requestBackToTripsList"
        @remove-trip="removeTrip"
        @add-trip="addTrip"
        @save-edit="saveEditTrip"
        @route-change="editTrip && loadEditDestinations(editTrip.route_id, true)"
      />

      <TimetableEditorStoptimesPanel
        v-if="detail && !isMobile && tab === 'stoptimes'"
        ref="stoptimesPanelRef"
        v-model:selected-service-id="selectedServiceId"
        :detail="detail"
        :overview="overview"
        :grid-overview-loading="gridOverviewLoading"
        :can-manage="cfg.canManage"
        :can-operate="cfg.canOperate"
        :view-mode="stoptimesView"
        @grid-toggle="onStoptimesGridToggle"
        @refresh-overview="loadOverview"
        @open-detail="openStoptimesDetail"
        @back="backToStoptimesList"
      />

      <TimetableEditorDeviationsTab
        v-if="detail && !isMobile && tab === 'deviations'"
        ref="deviationsTabRef"
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
