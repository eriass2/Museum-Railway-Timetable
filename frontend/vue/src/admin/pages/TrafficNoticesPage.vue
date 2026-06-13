<script setup lang="ts">
import TrafficNoticesForm from '../components/traffic-notices/TrafficNoticesForm.vue';
import TrafficNoticesFeedPreview from '../components/traffic-notices/TrafficNoticesFeedPreview.vue';
import TrafficNoticesList from '../components/traffic-notices/TrafficNoticesList.vue';
import { MrtAlert, MrtAsyncState } from '../components/ui';
import { useTrafficNoticesPage } from '../composables/traffic-notices/useTrafficNoticesPage';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import AdminMobilePageShell from '../components/mobile/AdminMobilePageShell.vue';
import { adminStr } from '../utils/adminLabels';

const { isMobile } = useMobileAdmin();
const {
  cfg,
  messages,
  viewMode,
  draft,
  charCountLabel,
  draftVisibilityLabel,
  saveMsg,
  loading,
  error,
  load,
  backToList,
  startCreate,
  startEdit,
  saveDraft,
  removeDraft,
  moveRow,
  feedRefreshKey,
} = useTrafficNoticesPage();
</script>

<template>
  <AdminMobilePageShell :mobile="isMobile">
    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'loading', 'Laddar…')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
      @retry="load"
    >
    <div class="mrt-vue-root">
      <TrafficNoticesFeedPreview
        v-if="viewMode === 'list'"
        :refresh-key="feedRefreshKey"
      />
      <TrafficNoticesList
        v-if="viewMode === 'list'"
        :messages="messages"
        :can-operate="cfg.canOperate"
        @create="startCreate"
        @edit="startEdit"
        @move="moveRow"
      />
      <TrafficNoticesForm
        v-else-if="draft"
        v-model:draft="draft"
        :view-mode="viewMode"
        :char-count-label="charCountLabel"
        :visibility-label="draftVisibilityLabel"
        @back="backToList"
        @save="saveDraft"
        @remove="removeDraft"
      />
      <MrtAlert v-if="saveMsg" context="admin" variant="success">{{ saveMsg }}</MrtAlert>
    </div>
    </MrtAsyncState>
  </AdminMobilePageShell>
</template>
