<script setup lang="ts">
import AdminLoadState from '../components/AdminLoadState.vue';
import TrafficNoticesForm from '../components/traffic-notices/TrafficNoticesForm.vue';
import TrafficNoticesFeedPreview from '../components/traffic-notices/TrafficNoticesFeedPreview.vue';
import TrafficNoticesList from '../components/traffic-notices/TrafficNoticesList.vue';
import { AdminStatusMessage } from '../components/ui';
import { useTrafficNoticesPage } from '../composables/traffic-notices/useTrafficNoticesPage';

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
  <AdminLoadState :loading="loading" :error="error" @retry="load">
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
      <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
    </div>
  </AdminLoadState>
</template>
