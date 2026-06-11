<script setup lang="ts">
import {
  AdminBackNav,
  AdminEmptyState,
  AdminFormActions,
  AdminPanel,
  AdminRowActions,
  AdminTableScroll,
  MrtButton,
} from '../ui';
import RoutePreview from './RoutePreview.vue';
import { lineKindLabelKey } from '../../utils/stations-routes/lineKindLabel';
import { adminFmt, adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import type { LineRow, StationRow } from '../../types';

export type LinesPanelView = 'list' | 'edit';

defineProps<{
  lines: LineRow[];
  stationsById: Map<number, { title: string; station_type: string }>;
  linesView: LinesPanelView;
}>();

const editingLine = defineModel<LineRow | null>('editingLine', { required: true });

const emit = defineEmits<{
  'back-line': [];
  'edit-line': [line: LineRow];
  'save-line': [];
}>();

const cfg = adminConfig();

function junctionLabel(line: LineRow): string {
  if (line.junction_station_name) {
    return line.junction_station_name;
  }
  return '—';
}
</script>

<template>
  <AdminPanel>
    <h2 class="screen-reader-text">{{ adminStr(cfg, 'stationsTabLines') }}</h2>

    <template v-if="linesView === 'edit' && editingLine">
      <AdminBackNav @back="emit('back-line')" />
      <h3 class="mrt-admin-line-editor__heading">
        {{ adminFmt(cfg, 'stationsEditLineTitle', editingLine.title) }}
      </h3>
      <p class="description">{{ adminStr(cfg, 'stationsLineStructureHint') }}</p>
      <div class="mrt-admin-line-editor__section">
        <label class="mrt-admin-line-editor__label" for="mrt-line-title">
          {{ adminStr(cfg, 'stationsLineTitleLabel') }}
        </label>
        <input
          id="mrt-line-title"
          v-model="editingLine.title"
          type="text"
          class="regular-text"
        />
        <p class="description mrt-admin-line-code-readonly">{{ editingLine.code }}</p>
      </div>
      <RoutePreview
        :station-ids="editingLine.station_ids"
        :stations-by-id="stationsById"
        :start-station-id="editingLine.start_station"
        :end-station-id="editingLine.end_station"
      />
      <AdminFormActions>
        <MrtButton context="admin" variant="primary" @click="emit('save-line')">
          {{ adminStr(cfg, 'stationsSaveLine') }}
        </MrtButton>
        <MrtButton context="admin" variant="secondary" @click="emit('back-line')">
          {{ adminStr(cfg, 'cancel') }}
        </MrtButton>
      </AdminFormActions>
    </template>

    <template v-else>
      <p class="description mrt-admin-lines-help">{{ adminStr(cfg, 'stationsLinesHelp') }}</p>
      <AdminEmptyState
        v-if="!lines.length"
        :title="adminStr(cfg, 'stationsEmptyLinesTitle')"
        :message="adminStr(cfg, 'stationsEmptyLinesMsg')"
      />
      <AdminTableScroll v-else>
        <table class="widefat striped mrt-admin-lines-table">
          <thead>
            <tr>
              <th>{{ adminStr(cfg, 'stationsColName') }}</th>
              <th>{{ adminStr(cfg, 'stationsColLineKind') }}</th>
              <th>{{ adminStr(cfg, 'stationsColJunction') }}</th>
              <th>{{ adminStr(cfg, 'stationsColStations') }}</th>
              <th v-if="cfg.canManage"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="line in lines" :key="line.code">
              <td>
                <span class="mrt-admin-line-title">{{ line.title }}</span>
                <span class="mrt-admin-line-code">{{ line.code }}</span>
              </td>
              <td>
                {{ adminStr(cfg, lineKindLabelKey(line.kind)) || line.kind || '—' }}
                <span v-if="line.bidirectional" class="mrt-admin-line-meta">
                  ({{ adminStr(cfg, 'stationsLinesBidirectional') }})
                </span>
              </td>
              <td>{{ junctionLabel(line) }}</td>
              <td>
                <RoutePreview
                  :station-ids="line.station_ids"
                  :stations-by-id="stationsById"
                  :start-station-id="line.start_station"
                  :end-station-id="line.end_station"
                  compact
                />
              </td>
              <td v-if="cfg.canManage">
                <AdminRowActions>
                  <MrtButton context="admin" variant="secondary" @click="emit('edit-line', line)">
                    {{ adminStr(cfg, 'edit') }}
                  </MrtButton>
                </AdminRowActions>
              </td>
            </tr>
          </tbody>
        </table>
      </AdminTableScroll>
    </template>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-lines-help {
  margin: 0 0 12px;
}

.mrt-admin-line-title {
  display: block;
  font-weight: 600;
}

.mrt-admin-line-code,
.mrt-admin-line-code-readonly {
  display: block;
  color: #646970;
  font-size: 12px;
}

.mrt-admin-line-meta {
  display: block;
  color: #646970;
  font-size: 12px;
}

.mrt-admin-line-editor__heading {
  margin: 0 0 12px;
  font-size: 14px;
  font-weight: 600;
}

.mrt-admin-line-editor__section {
  margin-bottom: 12px;
}

.mrt-admin-line-editor__label {
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
}
</style>
