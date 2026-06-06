<script setup lang="ts">
import { computed, ref } from 'vue';
import { getPrices, savePrices } from '../api/adminRest';
import type { PricesPayload } from '../api/adminRest';
import AdminLoadState from '../components/AdminLoadState.vue';
import {
  AdminDisclosure,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminStatusMessage,
  MrtButton,
} from '../components/ui';
import { usePriceSchemaEditor } from '../composables/usePriceSchemaEditor';
import { useAdminResource } from '../composables/useAdminResource';
import { useAdminSaveNotice } from '../composables/useAdminSaveNotice';
import { adminErrorMessage, adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { saveMsg, show: showSaved } = useAdminSaveNotice();
const data = ref<PricesPayload | null>(null);
const newTicketLabel = ref('');
const newCategoryLabel = ref('');
const newZone = ref('');

const { loading, error, load } = useAdminResource({
  beforeLoad: () => cfg.canManage,
  deniedMessage: adminStr(cfg, 'pricesNoPermission'),
  fetch: async () => {
    const payload = await getPrices();
    if (payload.zone_cap === undefined) {
      payload.zone_cap = 3;
    }
    if (!payload.afternoon_return) {
      payload.afternoon_return = {};
    }
    data.value = payload;
    ensureMatrixCells(payload);
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
  ensureMatrixCells,
} = usePriceSchemaEditor(data);

const ticketKeys = computed(() => Object.keys(data.value?.ticket_types ?? {}));
const categoryKeys = computed(() => Object.keys(data.value?.categories ?? {}));
const zones = computed(() => data.value?.zones ?? []);

function cellValue(ticket: string, category: string, zone: number): number | '' {
  const v = data.value?.matrix[ticket]?.[category]?.[zone];
  return v === null || v === undefined ? '' : v;
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

async function submit() {
  if (!data.value) return;
  error.value = '';
  try {
    data.value = await savePrices({
      matrix: data.value.matrix,
      ticket_types: data.value.ticket_types,
      categories: data.value.categories,
      zones: data.value.zones,
      zone_cap: data.value.zone_cap,
      afternoon_return: data.value.afternoon_return,
    });
    ensureMatrixCells(data.value);
    showSaved(adminStr(cfg, 'saved'));
  } catch (e) {
    error.value = adminErrorMessage(cfg, e, 'saveFailed');
  }
}
</script>

<template>
  <div>
    <h1>{{ adminStr(cfg, 'pricesTitle', 'Priser') }}</h1>

    <AdminLoadState
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'pricesLoading')"
      @retry="load"
    >
    <AdminPanel v-if="data">
    <form @submit.prevent="submit">
      <p class="description">
        {{ adminStr(cfg, 'pricesDescription') }}
      </p>

      <AdminDisclosure :summary="adminStr(cfg, 'pricesSchemaSummary')">
        <p class="description">{{ adminStr(cfg, 'pricesSchemaHint') }}</p>

        <h3 class="mrt-admin-prices-schema__heading">{{ adminStr(cfg, 'pricesTicketTypesHeading') }}</h3>
        <table class="widefat striped mrt-admin-prices-schema__table">
          <thead>
            <tr>
              <th>{{ adminStr(cfg, 'pricesSchemaKeyCol') }}</th>
              <th>{{ adminStr(cfg, 'pricesSchemaLabelCol') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="key in ticketKeys" :key="`ticket-${key}`">
              <td><code>{{ key }}</code></td>
              <td>
                <input v-model="data.ticket_types[key]" type="text" class="regular-text" />
              </td>
              <td>
                <MrtButton
                  context="admin"
                  variant="link-delete"
                  :disabled="ticketKeys.length <= 1"
                  @click="removeTicketType(key)"
                >
                  {{ adminStr(cfg, 'delete') }}
                </MrtButton>
              </td>
            </tr>
          </tbody>
        </table>
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

        <h3 class="mrt-admin-prices-schema__heading">{{ adminStr(cfg, 'pricesCategoriesHeading') }}</h3>
        <table class="widefat striped mrt-admin-prices-schema__table">
          <thead>
            <tr>
              <th>{{ adminStr(cfg, 'pricesSchemaKeyCol') }}</th>
              <th>{{ adminStr(cfg, 'pricesSchemaLabelCol') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="key in categoryKeys" :key="`cat-${key}`">
              <td><code>{{ key }}</code></td>
              <td>
                <input v-model="data.categories[key]" type="text" class="regular-text" />
              </td>
              <td>
                <MrtButton
                  context="admin"
                  variant="link-delete"
                  :disabled="categoryKeys.length <= 1"
                  @click="removeCategory(key)"
                >
                  {{ adminStr(cfg, 'delete') }}
                </MrtButton>
              </td>
            </tr>
          </tbody>
        </table>
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
              @click="removeZone(zone)"
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

        <h3 class="mrt-admin-prices-schema__heading">{{ adminStr(cfg, 'pricesAfternoonHeading') }}</h3>
        <p class="description">{{ adminStr(cfg, 'pricesAfternoonHint') }}</p>
        <table class="widefat striped mrt-admin-prices-schema__table">
          <thead>
            <tr>
              <th>{{ adminStr(cfg, 'pricesSchemaLabelCol') }}</th>
              <th>{{ adminStr(cfg, 'pricesAfternoonAmountCol') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="key in categoryKeys" :key="`afternoon-${key}`">
              <td>{{ data.categories[key] }}</td>
              <td>
                <input
                  v-model.number="data.afternoon_return[key]"
                  type="number"
                  min="0"
                  step="1"
                  class="small-text"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </AdminDisclosure>

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
              <th v-for="zone in zones" :key="`${cat}-${zone}`">{{ zone }}</th>
            </template>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in ticketKeys" :key="ticket">
            <th scope="row">{{ data.ticket_types[ticket] }}</th>
            <template v-for="cat in categoryKeys" :key="`${ticket}-${cat}`">
              <td v-for="zone in zones" :key="`${ticket}-${cat}-${zone}`">
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
</style>
