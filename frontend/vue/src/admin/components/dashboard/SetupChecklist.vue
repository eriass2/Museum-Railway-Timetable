<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  buildAdminSetupSteps,
  isAdminSetupComplete,
} from '../../utils/dashboard/adminSetupSteps';
import { adminConfig } from '../../types';
import { adminFmtN, adminStr } from '../../utils/adminLabels';
import { AdminDisclosure, AdminPanel } from '../ui';
import SetupChecklistSteps from './SetupChecklistSteps.vue';

const props = defineProps<{
  stats: Record<string, number>;
}>();

const cfg = adminConfig();
const router = useRouter();
const steps = computed(() => buildAdminSetupSteps(props.stats, cfg));
const complete = computed(() => isAdminSetupComplete(steps.value));
const doneCount = computed(() => steps.value.filter((s) => s.done).length);

function openStep(route: string) {
  void router.push(route);
}
</script>

<template>
  <AdminDisclosure
    v-if="complete"
    :summary="adminStr(cfg, 'setupTitle')"
    class="mrt-admin-setup mrt-admin-setup--complete"
  >
    <p class="description">{{ adminStr(cfg, 'setupCompleteSummary') }}</p>
    <SetupChecklistSteps :steps="steps" :complete="true" @go="openStep" />
  </AdminDisclosure>

  <AdminPanel v-else class="mrt-admin-setup" :title="adminStr(cfg, 'setupTitle')">
    <p class="description">
      {{ adminFmtN(cfg, 'setupProgress', { 1: doneCount, 2: steps.length }) }}
    </p>
    <SetupChecklistSteps :steps="steps" :complete="false" @go="openStep" />
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-setup--complete {
  margin-bottom: 16px;
}
</style>
