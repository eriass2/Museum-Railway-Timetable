<script setup lang="ts">
import type { PublicNoticeMessage } from '../../api/adminRestTrafficNotices';
import { AdminBackNav, AdminFormActions, AdminPanel, MrtButton } from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import {
  TRAFFIC_NOTICE_MAX_LENGTH,
  type TrafficNoticesViewMode,
} from '../../utils/traffic-notices/trafficNoticesAdmin';

defineProps<{
  viewMode: Exclude<TrafficNoticesViewMode, 'list'>;
  charCountLabel: string;
  visibilityLabel: string;
}>();

const draft = defineModel<PublicNoticeMessage>('draft', { required: true });

const emit = defineEmits<{
  back: [];
  save: [];
  remove: [];
}>();

const cfg = adminConfig();
</script>

<template>
  <AdminPanel>
    <AdminBackNav @back="emit('back')" />
    <h2>{{ adminStr(cfg, 'trafficNoticesTitle') }}</h2>
    <p class="notice notice-info">{{ adminStr(cfg, 'trafficNoticesVsDeviations') }}</p>
    <p class="description">{{ visibilityLabel }}</p>
    <p>
      <label>
        {{ adminStr(cfg, 'trafficNoticesTextLabel') }}
        <textarea
          v-model="draft.text"
          rows="4"
          class="large-text"
          :maxlength="TRAFFIC_NOTICE_MAX_LENGTH"
        />
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
      <MrtButton context="admin" variant="primary" @click="emit('save')">
        {{ adminStr(cfg, 'trafficNoticesSave') }}
      </MrtButton>
      <MrtButton
        v-if="viewMode === 'edit'"
        context="admin"
        variant="secondary"
        @click="emit('remove')"
      >
        {{ adminStr(cfg, 'trafficNoticesDelete') }}
      </MrtButton>
    </AdminFormActions>
  </AdminPanel>
</template>
