<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import {
  listTrafficNoticeMessages,
  saveTrafficNoticeMessages,
  type PublicNoticeMessage,
} from '../api/adminRestTrafficNotices';
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminBackNav,
  AdminEmptyState,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminStatusMessage,
  AdminTableScroll,
  MrtButton,
} from '../components/ui';
import { adminConfirm } from '../composables/adminConfirm';
import { proceedIfDiscardAllowed } from '../composables/adminDiscardGuard';
import { useAdminResource } from '../composables/useAdminResource';
import { useAdminSaveNotice } from '../composables/useAdminSaveNotice';
import { adminErrorMessage, adminFmtN, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';
import { noticeVisibleToday, reorderMessages, renumberSortOrder } from '../utils/trafficNoticesAdmin';

const MAX_LENGTH = 500;
type ViewMode = 'list' | 'edit' | 'create';

const cfg = adminConfig();
const messages = ref<PublicNoticeMessage[]>([]);
const viewMode = ref<ViewMode>('list');
const draft = ref<PublicNoticeMessage | null>(null);
const formSnapshot = ref('');
const { saveMsg, show: showSaveNotice } = useAdminSaveNotice();

const { loading, error, data, load } = useAdminResource({
  fetch: () => listTrafficNoticeMessages(),
  errorMessage: (e) => adminErrorMessage(cfg, e, 'loadFailed'),
});

watch(data, (res) => {
  if (!res) return;
  messages.value = [...res.messages].sort((a, b) => a.sort_order - b.sort_order);
});

const charCountLabel = computed(() => {
  const len = (draft.value?.text ?? '').length;
  return adminFmtN(cfg, 'trafficNoticesCharCount', { 1: len, 2: MAX_LENGTH });
});

function draftSnapshot(row: PublicNoticeMessage): string {
  return JSON.stringify(row);
}

function isFormDirty(): boolean {
  if (viewMode.value === 'list' || !draft.value) return false;
  return draftSnapshot(draft.value) !== formSnapshot.value;
}

function resetDraft(): void {
  draft.value = null;
  viewMode.value = 'list';
  formSnapshot.value = '';
}

async function backToList(): Promise<void> {
  if (viewMode.value !== 'list' && !(await proceedIfDiscardAllowed(isFormDirty()))) return;
  resetDraft();
}

function newDraft(): PublicNoticeMessage {
  const maxOrder = messages.value.reduce((max, row) => Math.max(max, row.sort_order), 0);
  return {
    id: `new-${Date.now()}`,
    text: '',
    enabled: true,
    active_from: '',
    active_to: '',
    sort_order: maxOrder + 10,
  };
}

function startCreate(): void {
  draft.value = newDraft();
  viewMode.value = 'create';
  formSnapshot.value = draftSnapshot(draft.value);
}

function startEdit(row: PublicNoticeMessage): void {
  draft.value = { ...row };
  viewMode.value = 'edit';
  formSnapshot.value = draftSnapshot(draft.value);
}

async function persistAll(next: PublicNoticeMessage[]): Promise<void> {
  const saved = await saveTrafficNoticeMessages(renumberSortOrder(next));
  messages.value = saved.messages;
  showSaveNotice(adminStr(cfg, 'trafficNoticesSaved'));
}

async function saveDraft(): Promise<void> {
  if (!draft.value || !draft.value.text.trim()) return;
  const row = { ...draft.value, text: draft.value.text.trim() };
  let next = [...messages.value];
  if (viewMode.value === 'create') {
    next.push(row);
  } else {
    next = next.map((item) => (item.id === row.id ? row : item));
  }
  await persistAll(next);
  resetDraft();
}

async function removeDraft(): Promise<void> {
  if (!draft.value || viewMode.value !== 'edit') return;
  if (!(await adminConfirm(adminStr(cfg, 'trafficNoticesDeleteConfirm')))) return;
  const next = messages.value.filter((row) => row.id !== draft.value?.id);
  await persistAll(next);
  resetDraft();
}

async function moveRow(index: number, direction: -1 | 1): Promise<void> {
  const target = index + direction;
  if (target < 0 || target >= messages.value.length) return;
  await persistAll(reorderMessages(messages.value, index, target));
}

function visibilityLabel(row: PublicNoticeMessage): string {
  if (!row.enabled) return adminStr(cfg, 'trafficNoticesInactive');
  return noticeVisibleToday(row)
    ? adminStr(cfg, 'trafficNoticesVisibleToday')
    : adminStr(cfg, 'trafficNoticesHiddenToday');
}
</script>

<template>
  <AdminLoadState :loading="loading" :error="error" @retry="load">
    <AdminPanel class="mrt-vue-root">
      <template v-if="viewMode === 'list'">
        <h2>{{ adminStr(cfg, 'trafficNoticesTitle') }}</h2>
        <p class="description">{{ adminStr(cfg, 'trafficNoticesIntro') }}</p>
        <p>
          <MrtButton context="admin" variant="primary" @click="startCreate">
            {{ adminStr(cfg, 'trafficNoticesNew') }}
          </MrtButton>
        </p>
        <AdminEmptyState v-if="messages.length === 0">
          {{ adminStr(cfg, 'trafficNoticesEmpty') }}
        </AdminEmptyState>
        <AdminTableScroll v-else>
          <table class="widefat striped">
            <thead>
              <tr>
                <th scope="col">{{ adminStr(cfg, 'trafficNoticesColText') }}</th>
                <th scope="col">{{ adminStr(cfg, 'trafficNoticesColFrom') }}</th>
                <th scope="col">{{ adminStr(cfg, 'trafficNoticesColTo') }}</th>
                <th scope="col">{{ adminStr(cfg, 'trafficNoticesColActive') }}</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in messages" :key="row.id">
                <td>{{ row.text }}</td>
                <td>{{ row.active_from || '—' }}</td>
                <td>{{ row.active_to || '—' }}</td>
                <td>{{ row.enabled ? '✓' : '—' }}</td>
                <td>
                  <AdminRowActions>
                    <MrtButton context="admin" variant="secondary" @click="moveRow(index, -1)">
                      {{ adminStr(cfg, 'trafficNoticesMoveUp') }}
                    </MrtButton>
                    <MrtButton context="admin" variant="secondary" @click="moveRow(index, 1)">
                      {{ adminStr(cfg, 'trafficNoticesMoveDown') }}
                    </MrtButton>
                    <MrtButton context="admin" variant="secondary" @click="startEdit(row)">
                      {{ adminStr(cfg, 'trafficNoticesEdit') }}
                    </MrtButton>
                  </AdminRowActions>
                </td>
              </tr>
            </tbody>
          </table>
        </AdminTableScroll>
      </template>

      <template v-else-if="draft">
        <AdminBackNav @back="backToList" />
        <h2>{{ adminStr(cfg, 'trafficNoticesTitle') }}</h2>
        <p class="description">{{ visibilityLabel(draft) }}</p>
        <p>
          <label>
            {{ adminStr(cfg, 'trafficNoticesTextLabel') }}
            <textarea v-model="draft.text" rows="4" class="large-text" :maxlength="MAX_LENGTH" />
          </label>
        </p>
        <p class="description">{{ charCountLabel }}</p>
        <p>
          <label>
            {{ adminStr(cfg, 'trafficNoticesColFrom') }}
            <input v-model="draft.active_from" type="date" />
          </label>
        </p>
        <p>
          <label>
            {{ adminStr(cfg, 'trafficNoticesColTo') }}
            <input v-model="draft.active_to" type="date" />
          </label>
        </p>
        <p>
          <label>
            <input v-model="draft.enabled" type="checkbox" />
            {{ adminStr(cfg, 'trafficNoticesEnabled') }}
          </label>
        </p>
        <AdminFormActions>
          <MrtButton context="admin" variant="primary" @click="saveDraft">
            {{ adminStr(cfg, 'trafficNoticesSave') }}
          </MrtButton>
          <MrtButton
            v-if="viewMode === 'edit'"
            context="admin"
            variant="secondary"
            @click="removeDraft"
          >
            {{ adminStr(cfg, 'trafficNoticesDelete') }}
          </MrtButton>
        </AdminFormActions>
      </template>

      <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
    </AdminPanel>
  </AdminLoadState>
</template>
