<script setup lang="ts">
import type { PublicNoticeMessage } from '../../api/adminRestTrafficNotices';
import {
  AdminEmptyState,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from '../ui';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';

defineProps<{
  messages: PublicNoticeMessage[];
  canOperate: boolean;
}>();

const emit = defineEmits<{
  create: [];
  edit: [row: PublicNoticeMessage];
  move: [index: number, direction: -1 | 1];
}>();

const cfg = adminConfig();
</script>

<template>
  <AdminPanel>
    <h2>{{ adminStr(cfg, 'trafficNoticesTitle') }}</h2>
    <p class="description">{{ adminStr(cfg, 'trafficNoticesIntro') }}</p>
    <p class="notice notice-info">{{ adminStr(cfg, 'trafficNoticesVsDeviations') }}</p>
    <p v-if="canOperate">
      <MrtButton context="admin" variant="primary" @click="emit('create')">
        {{ adminStr(cfg, 'trafficNoticesNew') }}
      </MrtButton>
    </p>
    <AdminEmptyState
      v-if="messages.length === 0"
      :title="adminStr(cfg, 'trafficNoticesTitle')"
      :message="adminStr(cfg, 'trafficNoticesEmpty')"
    />
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
              <AdminRowActions v-if="canOperate">
                <MrtButton
                  context="admin"
                  variant="secondary"
                  @click="emit('move', index, -1)"
                >
                  {{ adminStr(cfg, 'trafficNoticesMoveUp') }}
                </MrtButton>
                <MrtButton
                  context="admin"
                  variant="secondary"
                  @click="emit('move', index, 1)"
                >
                  {{ adminStr(cfg, 'trafficNoticesMoveDown') }}
                </MrtButton>
                <MrtButton context="admin" variant="secondary" @click="emit('edit', row)">
                  {{ adminStr(cfg, 'trafficNoticesEdit') }}
                </MrtButton>
              </AdminRowActions>
            </td>
          </tr>
        </tbody>
      </table>
    </AdminTableScroll>
  </AdminPanel>
</template>
