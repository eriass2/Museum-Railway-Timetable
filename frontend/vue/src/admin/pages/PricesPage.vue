<script setup lang="ts">
import { RouterLink } from 'vue-router';
import PricesAfternoonPanel from '../components/prices/PricesAfternoonPanel.vue';
import PricesPreview from '../components/prices/PricesPreview.vue';
import PricesTicketCopyPanel from '../components/prices/PricesTicketCopyPanel.vue';
import {
  AdminDisclosure,
  AdminFormActions,
  AdminInlineForm,
  AdminPanel,
  AdminTableScroll,
  AdminUnsavedBanner,
  MrtAlert,
  MrtAsyncState,
  MrtButton,
} from '../components/ui';
import { usePricesPage } from '../composables/prices/usePricesPage';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import AdminMobilePageShell from '../components/mobile/AdminMobilePageShell.vue';
import { adminFmtN, adminStr } from '../utils/adminLabels';
import { formatPriceZoneLabel } from '../../shared/priceZoneLabels';

const { isMobile } = useMobileAdmin();
const {
  cfg,
  data,
  afternoonThresholdMinutes,
  newTicketLabel,
  newCategoryLabel,
  newZone,
  copyZoneFrom,
  copyZoneTo,
  dirty,
  loading,
  error,
  saveMsg,
  ticketKeys,
  categoryKeys,
  zones,
  matrixConfigured,
  zonesBeyondCap,
  load,
  cellValue,
  cellIsEmpty,
  setCell,
  submitAddTicketType,
  submitAddCategory,
  submitAddZone,
  submitCopyZone,
  confirmRemoveTicketType,
  confirmRemoveCategory,
  confirmRemoveZone,
  submit,
} = usePricesPage();
</script>

<template>
  <AdminMobilePageShell :mobile="isMobile">
    <h1>{{ adminStr(cfg, 'pricesTitle', 'Priser') }}</h1>

    <MrtAsyncState
      context="admin"
      :loading="loading"
      :error="error"
      :loading-text="adminStr(cfg, 'pricesLoading')"
      :retry-label="adminStr(cfg, 'retry', 'Försök igen')"
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

          <PricesTicketCopyPanel v-model="data" />

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
                    {{ formatPriceZoneLabel(zone) }}
                  </option>
                </select>
              </label>
              <label>
                {{ adminStr(cfg, 'pricesCopyZoneTo') }}
                <select v-model.number="copyZoneTo">
                  <option value="">—</option>
                  <option v-for="zone in zones" :key="`copy-to-${zone}`" :value="zone">
                    {{ formatPriceZoneLabel(zone) }}
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
                {{ adminStr(cfg, 'pricesZoneLabel', 'Zon') }} {{ formatPriceZoneLabel(zone) }}
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
                      {{ formatPriceZoneLabel(zone) }}
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
            <MrtAlert v-if="saveMsg" context="admin" variant="success">{{ saveMsg }}</MrtAlert>
          </AdminFormActions>
        </form>
      </AdminPanel>
    </MrtAsyncState>
  </AdminMobilePageShell>
</template>

<style scoped>
.mrt-admin-prices-schema__heading {
  margin: 16px 0 8px;
  font-size: 13px;
}

.mrt-admin-prices-onboarding__title {
  margin: 16px 0 8px;
  font-size: 1em;
}

.mrt-admin-prices-onboarding__steps {
  margin-bottom: 16px;
}

.mrt-admin-help-steps {
  margin: 0 0 0 1.5em;
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
