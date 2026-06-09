<script setup lang="ts">
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { getPrices, getSettings, savePrices, saveSettings } from '../api/adminRest';
import type { PricesPayload, SettingsPayload } from '../api/adminRest';
import AdminLoadState from '../components/AdminLoadState.vue';
import PricesAfternoonPanel from '../components/prices/PricesAfternoonPanel.vue';
import PricesPreview from '../components/prices/PricesPreview.vue';
import {
  AdminDisclosure,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminStatusMessage,
  AdminTableScroll,
  AdminUnsavedBanner,
  MrtButton,
} from '../components/ui';
import { usePriceSchemaEditor } from '../composables/prices/usePriceSchemaEditor';
import { useAdminResource } from '../composables/useAdminResource';
import { useAdminSaveNotice } from '../composables/useAdminSaveNotice';
import { useAdminFormDirty } from '../composables/useAdminFormDirty';
import { useAdminUnsavedGuard } from '../composables/useAdminUnsavedGuard';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import { adminConfirm } from '../composables/adminConfirm';
import { adminErrorMessage, adminFmtN, adminStr } from '../utils/adminLabels';
import {
  hasMatrixZonesBeyondCap,
  priceMatrixHasAnyValue,
} from '../utils/prices/adminPricePreview';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const { saveMsg, show: showSaved } = useAdminSaveNotice();
const data = ref<PricesPayload | null>(null);
const settingsSnapshot = ref<SettingsPayload | null>(null);
const afternoonThresholdMinutes = ref(900);
const savedThresholdMinutes = ref(900);
const newTicketLabel = ref('');
const newCategoryLabel = ref('');
const newZone = ref('');
const copyZoneFrom = ref<number | ''>('');
const copyZoneTo = ref<number | ''>('');

const { dirty: pricesDirty, syncSnapshot } = useAdminFormDirty(data);
const thresholdDirty = computed(
  () => afternoonThresholdMinutes.value !== savedThresholdMinutes.value,
);
const dirty = computed(() => pricesDirty.value || thresholdDirty.value);
useAdminUnsavedGuard(dirty);

function syncAllSnapshots() {
  syncSnapshot();
  savedThresholdMinutes.value = afternoonThresholdMinutes.value;
}

const { loading, error, load } = useAdminResource({
  beforeLoad: () => cfg.canManage,
  deniedMessage: adminStr(cfg, 'pricesNoPermission'),
  fetch: async () => {
    const [payload, settings] = await Promise.all([getPrices(), getSettings()]);
    settingsSnapshot.value = { ...settings };
    afternoonThresholdMinutes.value = settings.afternoon_return_threshold_minutes;
    savedThresholdMinutes.value = settings.afternoon_return_threshold_minutes;
    if (payload.zone_cap === undefined) {
      payload.zone_cap = 3;
    }
    if (!payload.afternoon_return) {
      payload.afternoon_return = {};
    }
    data.value = payload;
    ensureMatrixCells(payload);
    syncAllSnapshots();
    return payload;
  },
  errorMessage: (e) => {
    data.value = null;
    return adminErrorMessage(cfg, e, 'pricesLoadFailed');
  },
});

const {
  addTicketType,
  removeTicketType,
  addCategory,
  removeCategory,
  addZone,
  removeZone,
  copyZonePrices,
  ensureMatrixCells,
} = usePriceSchemaEditor(data);

const ticketKeys = computed(() => Object.keys(data.value?.ticket_types ?? {}));
const categoryKeys = computed(() => Object.keys(data.value?.categories ?? {}));
const zones = computed(() => data.value?.zones ?? []);
const matrixConfigured = computed(() => (data.value ? priceMatrixHasAnyValue(data.value) : false));
const zonesBeyondCap = computed(() =>
  data.value ? hasMatrixZonesBeyondCap(data.value) : false,
);

function cellValue(ticket: string, category: string, zone: number): number | '' {
  const v = data.value?.matrix[ticket]?.[category]?.[zone];
  return v === null || v === undefined ? '' : v;
}

function cellIsEmpty(ticket: string, category: string, zone: number): boolean {
  return cellValue(ticket, category, zone) === '';
}

function setCell(ticket: string, category: string, zone: number, raw: string) {
  if (!data.value) return;
  if (!data.value.matrix[ticket]) data.value.matrix[ticket] = {};
  if (!data.value.matrix[ticket][category]) data.value.matrix[ticket][category] = {};
  data.value.matrix[ticket][category][zone] = raw === '' ? null : Number(raw);
}

function submitAddTicketType() {
  addTicketType(newTicketLabel.value);
  newTicketLabel.value = '';
}

function submitAddCategory() {
  addCategory(newCategoryLabel.value);
  newCategoryLabel.value = '';
}

function submitAddZone() {
  const zone = parseInt(newZone.value, 10);
  if (!Number.isFinite(zone)) {
    return;
  }
  addZone(zone);
  newZone.value = '';
}

function submitCopyZone() {
  if (copyZoneFrom.value === '' || copyZoneTo.value === '') {
    return;
  }
  copyZonePrices(Number(copyZoneFrom.value), Number(copyZoneTo.value));
}

async function confirmRemoveTicketType(key: string) {
  if (!data.value) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'pricesDeleteTicketTitle'),
    message: adminFmtN(cfg, 'pricesDeleteTicketMsg', { 1: data.value.ticket_types[key] ?? key }),
    danger: true,
  });
  if (ok) {
    removeTicketType(key);
  }
}

async function confirmRemoveCategory(key: string) {
  if (!data.value) return;
  const ok = await adminConfirm({
    title: adminStr(cfg, 'pricesDeleteCategoryTitle'),
    message: adminFmtN(cfg, 'pricesDeleteCategoryMsg', { 1: data.value.categories[key] ?? key }),
    danger: true,
  });
  if (ok) {
    removeCategory(key);
  }
}

async function confirmRemoveZone(zone: number) {
  const ok = await adminConfirm({
    title: adminStr(cfg, 'pricesDeleteZoneTitle'),
    message: adminFmtN(cfg, 'pricesDeleteZoneMsg', { 1: String(zone) }),
    danger: true,
  });
  if (ok) {
    removeZone(zone);
  }
}

async function submit() {
  if (!data.value) return;
  error.value = '';
  try {
    const tasks: Promise<unknown>[] = [
      savePrices({
        matrix: data.value.matrix,
        ticket_types: data.value.ticket_types,
        categories: data.value.categories,
        zones: data.value.zones,
        zone_cap: data.value.zone_cap,
        afternoon_return: data.value.afternoon_return,
      }),
    ];
    if (settingsSnapshot.value && thresholdDirty.value) {
      tasks.push(
        saveSettings({
          ...settingsSnapshot.value,
          afternoon_return_threshold_minutes: afternoonThresholdMinutes.value,
        }),
      );
    }
    const results = await Promise.all(tasks);
    data.value = results[0] as PricesPayload;
    if (results[1]) {
      settingsSnapshot.value = results[1] as SettingsPayload;
      afternoonThresholdMinutes.value = settingsSnapshot.value.afternoon_return_threshold_minutes;
    }
    ensureMatrixCells(data.value);
    syncAllSnapshots();
    showSaved(adminStr(cfg, 'saved'));
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'saveFailed');
  }
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'pricesTitle', 'Priser') }}</h1>

    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'pricesLoading')"
      @retry="load"
    >
      <AdminPanel v-if="data">
        <form @submit.prevent="submit">
          <AdminUnsavedBanner :show="dirty" :message="adminStr(cfg, 'pricesUnsaved')" />

          <p v-if="!matrixConfigured" class="notice notice-warning">
            {{ adminStr(cfg, 'pricesEmptyMatrix') }}
          </p>

          <p class="description">
            {{ adminStr(cfg, 'pricesDescription') }}
            {{ adminStr(cfg, 'pricesHelpIntro') }}
            <RouterLink :to="{ path: '/help', query: { section: 'price-zones' } }">
              {{ adminStr(cfg, 'pricesHelpLink') }}
            </RouterLink>
          </p>

          <h2 class="mrt-admin-prices-onboarding__title">{{ adminStr(cfg, 'pricesOnboardingTitle') }}</h2>
          <ol class="mrt-admin-help-steps mrt-admin-prices-onboarding__steps">
            <li>{{ adminStr(cfg, 'pricesOnboardingStep1') }}</li>
            <li>{{ adminStr(cfg, 'pricesOnboardingStep2') }}</li>
            <li>{{ adminStr(cfg, 'pricesOnboardingStep3') }}</li>
          </ol>

          <PricesPreview :payload="data" />

          <PricesAfternoonPanel
            :payload="data"
            :threshold-minutes="afternoonThresholdMinutes"
            @update:threshold-minutes="afternoonThresholdMinutes = $event"
          />

          <p v-if="matrixConfigured" class="description mrt-admin-prices-zone-cap-status">
            {{
              adminFmtN(cfg, 'pricesZoneCapStatus', {
                1: String(data.zone_cap),
              })
            }}
          </p>
          <p v-if="zonesBeyondCap" class="description mrt-admin-prices-zone-cap-notice">
            {{
              adminFmtN(cfg, 'pricesMatrixZoneCapNotice', {
                1: String(data.zone_cap),
              })
            }}
          </p>

          <AdminDisclosure :summary="adminStr(cfg, 'pricesCopyZoneHeading')">
            <p class="description">{{ adminStr(cfg, 'pricesCopyZoneHint') }}</p>
            <AdminInlineForm>
              <label>
                {{ adminStr(cfg, 'pricesCopyZoneFrom') }}
                <select v-model.number="copyZoneFrom">
                  <option value="">—</option>
                  <option v-for="zone in zones" :key="`copy-from-${zone}`" :value="zone">
                    {{ zone }}
                  </option>
                </select>
              </label>
              <label>
                {{ adminStr(cfg, 'pricesCopyZoneTo') }}
                <select v-model.number="copyZoneTo">
                  <option value="">—</option>
                  <option v-for="zone in zones" :key="`copy-to-${zone}`" :value="zone">
                    {{ zone }}
                  </option>
                </select>
              </label>
              <MrtButton context="admin" variant="secondary" type="button" @click="submitCopyZone">
                {{ adminStr(cfg, 'pricesCopyZoneButton') }}
              </MrtButton>
            </AdminInlineForm>
          </AdminDisclosure>

          <AdminDisclosure :summary="adminStr(cfg, 'pricesSchemaSummary')">
            <p class="description">{{ adminStr(cfg, 'pricesSchemaHint') }}</p>

            <h3 class="mrt-admin-prices-schema__heading">
              {{ adminStr(cfg, 'pricesTicketTypesHeading') }}
            </h3>
            <AdminTableScroll>
              <table class="widefat striped mrt-admin-prices-schema__table mrt-admin-responsive-table">
                <thead>
                  <tr>
                    <th>{{ adminStr(cfg, 'pricesSchemaKeyCol') }}</th>
                    <th>{{ adminStr(cfg, 'pricesSchemaLabelCol') }}</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="key in ticketKeys" :key="`ticket-${key}`">
                    <td :data-label="adminStr(cfg, 'pricesSchemaKeyCol')"><code>{{ key }}</code></td>
                    <td :data-label="adminStr(cfg, 'pricesSchemaLabelCol')">
                      <input v-model="data.ticket_types[key]" type="text" class="regular-text" />
                    </td>
                    <td>
                      <MrtButton
                        context="admin"
                        variant="link-delete"
                        :disabled="ticketKeys.length <= 1"
                        @click="confirmRemoveTicketType(key)"
                      >
                        {{ adminStr(cfg, 'delete') }}
                      </MrtButton>
                    </td>
                  </tr>
                </tbody>
              </table>
            </AdminTableScroll>
            <AdminInlineForm>
              <input
                v-model="newTicketLabel"
                type="text"
                class="regular-text"
                :placeholder="adminStr(cfg, 'pricesNewTicketPlaceholder')"
              />
              <MrtButton context="admin" variant="secondary" type="button" @click="submitAddTicketType">
                {{ adminStr(cfg, 'add') }}
              </MrtButton>
            </AdminInlineForm>

            <h3 class="mrt-admin-prices-schema__heading">
              {{ adminStr(cfg, 'pricesCategoriesHeading') }}
            </h3>
            <AdminTableScroll>
              <table class="widefat striped mrt-admin-prices-schema__table mrt-admin-responsive-table">
                <thead>
                  <tr>
                    <th>{{ adminStr(cfg, 'pricesSchemaKeyCol') }}</th>
                    <th>{{ adminStr(cfg, 'pricesSchemaLabelCol') }}</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="key in categoryKeys" :key="`cat-${key}`">
                    <td :data-label="adminStr(cfg, 'pricesSchemaKeyCol')"><code>{{ key }}</code></td>
                    <td :data-label="adminStr(cfg, 'pricesSchemaLabelCol')">
                      <input v-model="data.categories[key]" type="text" class="regular-text" />
                    </td>
                    <td>
                      <MrtButton
                        context="admin"
                        variant="link-delete"
                        :disabled="categoryKeys.length <= 1"
                        @click="confirmRemoveCategory(key)"
                      >
                        {{ adminStr(cfg, 'delete') }}
                      </MrtButton>
                    </td>
                  </tr>
                </tbody>
              </table>
            </AdminTableScroll>
            <AdminInlineForm>
              <input
                v-model="newCategoryLabel"
                type="text"
                class="regular-text"
                :placeholder="adminStr(cfg, 'pricesNewCategoryPlaceholder')"
              />
              <MrtButton context="admin" variant="secondary" type="button" @click="submitAddCategory">
                {{ adminStr(cfg, 'add') }}
              </MrtButton>
            </AdminInlineForm>

            <h3 class="mrt-admin-prices-schema__heading">{{ adminStr(cfg, 'pricesZonesHeading') }}</h3>
            <p class="mrt-admin-prices-schema__zones">
              <span v-for="zone in zones" :key="`zone-${zone}`" class="mrt-admin-prices-schema__zone">
                {{ adminStr(cfg, 'pricesZoneLabel', 'Zon') }} {{ zone }}
                <MrtButton
                  context="admin"
                  variant="link-delete"
                  :disabled="zones.length <= 1"
                  @click="confirmRemoveZone(zone)"
                >
                  ×
                </MrtButton>
              </span>
            </p>
            <AdminInlineForm>
              <input
                v-model="newZone"
                type="number"
                min="1"
                max="99"
                class="small-text"
                :placeholder="adminStr(cfg, 'pricesNewZonePlaceholder')"
              />
              <MrtButton context="admin" variant="secondary" type="button" @click="submitAddZone">
                {{ adminStr(cfg, 'add') }}
              </MrtButton>
            </AdminInlineForm>

            <h3 class="mrt-admin-prices-schema__heading">{{ adminStr(cfg, 'pricesZoneCapHeading') }}</h3>
            <p class="description">{{ adminStr(cfg, 'pricesZoneCapHint') }}</p>
            <input
              v-model.number="data.zone_cap"
              type="number"
              min="1"
              max="99"
              class="small-text"
            />
          </AdminDisclosure>

          <AdminTableScroll>
            <table class="widefat striped mrt-price-matrix-table">
              <thead>
                <tr>
                  <th>{{ adminStr(cfg, 'pricesTicketTypeCol') }}</th>
                  <th v-for="cat in categoryKeys" :key="cat" :colspan="zones.length">
                    {{ data.categories[cat] }}
                  </th>
                </tr>
                <tr>
                  <th>{{ adminStr(cfg, 'pricesZonesCol') }}</th>
                  <template v-for="cat in categoryKeys" :key="`z-${cat}`">
                    <th
                      v-for="zone in zones"
                      :key="`${cat}-${zone}`"
                      :class="{
                        'mrt-admin-price-zone--beyond-cap': zone > data.zone_cap,
                      }"
                      :title="
                        zone > data.zone_cap
                          ? adminFmtN(cfg, 'pricesZoneBeyondCapTitle', { 1: String(data.zone_cap) })
                          : undefined
                      "
                    >
                      {{ zone }}
                    </th>
                  </template>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in ticketKeys" :key="ticket">
                  <th scope="row">{{ data.ticket_types[ticket] }}</th>
                  <template v-for="cat in categoryKeys" :key="`${ticket}-${cat}`">
                    <td
                      v-for="zone in zones"
                      :key="`${ticket}-${cat}-${zone}`"
                      :class="{
                        'mrt-admin-price-cell--empty': cellIsEmpty(ticket, cat, zone),
                        'mrt-admin-price-zone--beyond-cap': zone > data.zone_cap,
                      }"
                    >
                      <input
                        type="number"
                        min="0"
                        step="1"
                        class="small-text"
                        :value="cellValue(ticket, cat, zone)"
                        placeholder="—"
                        @input="setCell(ticket, cat, zone, ($event.target as HTMLInputElement).value)"
                      />
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </AdminTableScroll>

          <AdminFormActions>
            <MrtButton context="admin" variant="primary" type="submit">
              {{ adminStr(cfg, 'pricesSaveButton') }}
            </MrtButton>
            <AdminStatusMessage v-if="saveMsg" :message="saveMsg" />
          </AdminFormActions>
        </form>
      </AdminPanel>
    </AdminLoadState>
  </div>
</template>

<style scoped>
.mrt-admin-prices-schema__heading {
  margin: 16px 0 8px;
  font-size: 13px;
}

.mrt-admin-prices-schema__table {
  margin-bottom: 8px;
}

.mrt-admin-prices-schema__zones {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.mrt-admin-prices-schema__zone {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  background: #f0f0f1;
  border-radius: 3px;
}

.mrt-admin-price-cell--empty {
  background: #fcf9e8;
}

:deep(.mrt-admin-price-zone--beyond-cap) {
  color: #787c82;
  background: #f6f7f7;
}
</style>
