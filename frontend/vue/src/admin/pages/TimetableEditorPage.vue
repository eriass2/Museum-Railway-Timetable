<script setup lang="ts">
import { computed } from 'vue';
import { overviewUiLabels } from '../../shared/overviewUiLabels';
import MrtTimetableOverviewView from '../../components/overview/MrtTimetableOverviewView.vue';
import TimetableEditorMetaPanel from '../components/timetable-editor/TimetableEditorMetaPanel.vue';
import TimetableEditorStoptimesPanel from '../components/timetable-editor/TimetableEditorStoptimesPanel.vue';
import TimetableEditorDatesTab from '../components/timetable-editor/TimetableEditorDatesTab.vue';
import TimetableEditorDeviationsTab from '../components/timetable-editor/TimetableEditorDeviationsTab.vue';
import TimetableEditorGridTab from '../components/timetable-editor/TimetableEditorGridTab.vue';
import TimetableEditorTripsTab from '../components/timetable-editor/TimetableEditorTripsTab.vue';
import MobileTimetablePanel from '../components/mobile/MobileTimetablePanel.vue';
import { AdminPanel, MrtAlert, MrtAsyncState } from '../components/ui';
import { useTimetableEditorPage } from '../composables/timetable-editor/useTimetableEditorPage';
import type { TimetableEditorTab } from '../composables/timetable-editor/useTimetableEditorPage';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import AdminMobilePageShell from '../components/mobile/AdminMobilePageShell.vue';
import { adminStr } from '../utils/adminLabels';
import { buildTimetableEditorDesktopTabs } from '../utils/timetable-editor/editorDesktopTabs';

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
  editTrip,
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
  if (isMobile.value) {
    return [];
  }
  return buildTimetableEditorDesktopTabs(cfg);
});

function onTabClick(next: TimetableEditorTab): void {
  void switchTab(next);
}
</script>

<template>
  <AdminMobilePageShell :mobile="isMobile">
    <h1 v-if="!detail">{{ adminStr(cfg, 'editorTitle') }}</h1>
    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'editorLoading')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
      @retry="loadDetail"
    >
      <MrtAlert v-if="saveMsg" context="admin" variant="success">{{ saveMsg }}</MrtAlert>

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

      <TimetableEditorGridTab
        v-if="detail && !isMobile && tab === 'grid'"
        :overview="overview"
        :loading="gridOverviewLoading"
        :can-manage="cfg.canManage"
        :can-operate="cfg.canOperate"
        @refresh="loadOverview"
      />

      <TimetableEditorTripsTab
        v-if="detail && !isMobile && tab === 'trips'"
        v-model:new-trip="newTrip"
        v-model:edit-trip="editTrip"
        :can-manage="cfg.canManage"
        :detail="detail"
        :view-mode="tripsView"
        :train-type-icon-key="trainTypeIconKey"
        @open-stoptimes="openStoptimes"
        @start-create="startCreateTrip"
        @start-edit="startEditTrip"
        @back="requestBackToTripsList"
        @remove-trip="removeTrip"
        @add-trip="addTrip"
        @save-edit="saveEditTrip"
      />

      <TimetableEditorStoptimesPanel
        v-if="detail && !isMobile && tab === 'stoptimes'"
        ref="stoptimesPanelRef"
        v-model:selected-service-id="selectedServiceId"
        :detail="detail"
        :can-manage="cfg.canManage"
        :can-operate="cfg.canOperate"
        :view-mode="stoptimesView"
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
    </MrtAsyncState>
  </AdminMobilePageShell>
</template>
