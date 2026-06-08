<script setup lang="ts">
import AdminLoadState from '../components/AdminLoadState.vue';
import AdminTrafficNoticesForm from '../components/AdminTrafficNoticesForm.vue';
import AdminTrafficNoticesList from '../components/AdminTrafficNoticesList.vue';
import { AdminStatusMessage } from '../components/ui';
import { useTrafficNoticesPage } from '../composables/useTrafficNoticesPage';

const {
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
} = useTrafficNoticesPage();
</script>

<template>
  <AdminLoadState :loading="loading" :error="error" @retry="load">
    <div class="mrt-vue-root">
      <AdminTrafficNoticesList
        v-if="viewMode === 'list'"
        :messages="messages"
        @create="startCreate"
        @edit="startEdit"
        @move="moveRow"
      />
      <AdminTrafficNoticesForm
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
