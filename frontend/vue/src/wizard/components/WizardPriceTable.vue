<script setup lang="ts">
import MrtPriceTable from '../../components/ui/MrtPriceTable.vue';
import type { TripType } from '../types';
import type { WizardCfg } from '../utils/wizardCfgTypes';
import type { MaybeRef } from 'vue';

defineProps<{
  cfg: MaybeRef<WizardCfg>;
  tripType: MaybeRef<TripType>;
  fromId: MaybeRef<number>;
  toId: MaybeRef<number>;
  compactTitle?: boolean;
}>();
</script>

<template>
  <MrtPriceTable
    :cfg="cfg"
    :trip-type="tripType"
    :from-id="fromId"
    :to-id="toId"
    :show-zone-count="!compactTitle"
  />
</template>
