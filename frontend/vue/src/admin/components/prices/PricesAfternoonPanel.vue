<script setup lang="ts">
import { computed, toRef } from 'vue';
import MrtPriceTable from '../../../components/ui/MrtPriceTable.vue';
import type { PricesPayload } from '../../api/adminRest';
import { AdminTableScroll } from '../ui';
import { formatPriceZoneLabel } from '../../../shared/priceZoneLabels';
import { adminFmtN, adminStr } from '../../utils/adminLabels';
import { minutesToTimeInput, timeInputToMinutes } from '../../utils/settingsTime';
import { adminConfig } from '../../types';
import { useAfternoonPriceCompare } from '../../composables/prices/useAfternoonPriceCompare';

const props = defineProps<{
  payload: PricesPayload;
  thresholdMinutes: number;
}>();

const emit = defineEmits<{
  'update:thresholdMinutes': [value: number];
}>();

const cfg = adminConfig();
const categoryKeys = computed(() => Object.keys(props.payload.categories));
const afternoonActive = computed(() => props.thresholdMinutes > 0);

const {
  compareZone,
  pricingZones,
  priceCfg,
  normalReturn,
  afternoonReturn,
  compareLabels,
  normalCompareLabels,
} = useAfternoonPriceCompare(toRef(props, 'payload'), toRef(props, 'thresholdMinutes'), cfg);

function onThresholdInput(event: Event) {
  emit('update:thresholdMinutes', timeInputToMinutes((event.target as HTMLInputElement).value));
}
</script>

<template>
  <section class="mrt-admin-prices-afternoon">
    <h2 class="mrt-admin-prices-afternoon__heading">
      {{ adminStr(cfg, 'pricesAfternoonHeading') }}
    </h2>
    <p class="description">{{ adminStr(cfg, 'pricesAfternoonRule') }}</p>

    <div class="mrt-admin-prices-afternoon__threshold">
      <label>
        {{ adminStr(cfg, 'pricesAfternoonThreshold') }}
        <input
          :value="minutesToTimeInput(thresholdMinutes)"
          type="time"
          @input="onThresholdInput"
        />
      </label>
      <p v-if="!afternoonActive" class="description mrt-admin-prices-afternoon__disabled">
        {{ adminStr(cfg, 'pricesAfternoonDisabledHint') }}
      </p>
      <p v-else class="description">
        {{
          adminFmtN(cfg, 'pricesAfternoonThresholdActive', {
            1: minutesToTimeInput(thresholdMinutes),
          })
        }}
      </p>
    </div>

    <AdminTableScroll>
      <table class="widefat striped mrt-admin-prices-schema__table mrt-admin-responsive-table">
        <thead>
          <tr>
            <th>{{ adminStr(cfg, 'pricesSchemaLabelCol') }}</th>
            <th>{{ adminStr(cfg, 'pricesAfternoonAmountCol') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="key in categoryKeys" :key="`afternoon-${key}`">
            <td :data-label="adminStr(cfg, 'pricesSchemaLabelCol')">{{ payload.categories[key] }}</td>
            <td :data-label="adminStr(cfg, 'pricesAfternoonAmountCol')">
              <input
                v-model.number="payload.afternoon_return[key]"
                type="number"
                min="0"
                step="1"
                class="small-text"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </AdminTableScroll>

    <h3 class="mrt-admin-prices-afternoon__subheading">
      {{ adminStr(cfg, 'pricesAfternoonCompareTitle') }}
    </h3>
    <p class="description">{{ adminStr(cfg, 'pricesAfternoonCompareHint') }}</p>
    <label class="mrt-admin-prices-afternoon__zone">
      {{ adminStr(cfg, 'pricesPreviewZone') }}
      <select v-model.number="compareZone">
        <option v-for="zone in pricingZones" :key="`afternoon-compare-${zone}`" :value="zone">
          {{ formatPriceZoneLabel(zone) }}
        </option>
      </select>
    </label>

    <div class="mrt-admin-prices-afternoon-compare">
      <div class="mrt-admin-prices-afternoon-compare__col">
        <p class="mrt-admin-prices-afternoon-compare__label">
          {{ adminStr(cfg, 'pricesAfternoonCompareNormal') }}
        </p>
        <MrtPriceTable
          :price-cfg="priceCfg"
          :labels="normalCompareLabels"
          :trip-price="normalReturn"
          :show-all-types="false"
        />
      </div>
      <div class="mrt-admin-prices-afternoon-compare__col">
        <p class="mrt-admin-prices-afternoon-compare__label">
          {{ adminStr(cfg, 'pricesAfternoonCompareAfternoon') }}
        </p>
        <MrtPriceTable
          :price-cfg="priceCfg"
          :labels="compareLabels"
          :trip-price="afternoonReturn"
          :show-all-types="false"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
.mrt-admin-prices-afternoon {
  margin: 24px 0;
  padding: 16px 0 8px;
  border-top: 1px solid #dcdcde;
}

.mrt-admin-prices-afternoon__heading {
  margin: 0 0 8px;
  font-size: 14px;
}

.mrt-admin-prices-afternoon__subheading {
  margin: 20px 0 8px;
  font-size: 13px;
}

.mrt-admin-prices-afternoon__threshold label,
.mrt-admin-prices-afternoon__zone {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.mrt-admin-prices-afternoon-compare {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin-top: 8px;
}

.mrt-admin-prices-afternoon-compare__label {
  margin: 0 0 4px;
  font-weight: 600;
}

@media (max-width: 782px) {
  .mrt-admin-prices-afternoon-compare {
    grid-template-columns: 1fr;
  }

  .mrt-admin-prices-afternoon__threshold label,
  .mrt-admin-prices-afternoon__zone {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
