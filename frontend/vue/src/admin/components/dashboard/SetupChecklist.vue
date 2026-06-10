<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  buildAdminSetupSteps,
  isAdminSetupComplete,
} from '../../utils/dashboard/adminSetupSteps';
import { adminConfig } from '../../types';
import { adminFmtN, adminStr } from '../../utils/adminLabels';
import { AdminDisclosure, AdminPanel, MrtButton } from '../ui';

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
    <ol class="mrt-admin-setup__list">
      <li
        v-for="step in steps"
        :key="step.id"
        class="mrt-admin-setup__item mrt-admin-setup__item--done"
      >
        <span class="mrt-admin-setup__status" aria-hidden="true">✓</span>
        <span class="mrt-admin-setup__label">{{ step.label }}</span>
        <MrtButton
          context="admin"
          variant="small"
          class="mrt-admin-setup__go"
          @click="openStep(step.route)"
        >
          {{ adminStr(cfg, 'setupGo') }}
        </MrtButton>
      </li>
    </ol>
  </AdminDisclosure>

  <AdminPanel v-else class="mrt-admin-setup" :title="adminStr(cfg, 'setupTitle')">
    <p class="description">
      {{ adminFmtN(cfg, 'setupProgress', { 1: doneCount, 2: steps.length }) }}
    </p>
    <ol class="mrt-admin-setup__list">
      <li
        v-for="step in steps"
        :key="step.id"
        class="mrt-admin-setup__item"
        :class="{ 'mrt-admin-setup__item--done': step.done }"
      >
        <span class="mrt-admin-setup__status" aria-hidden="true">{{ step.done ? '✓' : '○' }}</span>
        <span class="mrt-admin-setup__label">{{ step.label }}</span>
        <MrtButton
          v-if="!step.done"
          context="admin"
          variant="small"
          class="mrt-admin-setup__go"
          @click="openStep(step.route)"
        >
          {{ adminStr(cfg, 'setupGo') }}
        </MrtButton>
      </li>
    </ol>
  </AdminPanel>
</template>
