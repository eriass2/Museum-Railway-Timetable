<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  buildAdminSetupSteps,
  isAdminSetupComplete,
} from '../utils/adminSetupSteps';
import { adminConfig } from '../types';
import { adminFmtN, adminStr } from '../utils/adminLabels';
import { AdminPanel } from './ui';

const props = defineProps<{
  stats: Record<string, number>;
}>();

const cfg = adminConfig();
const router = useRouter();
const steps = computed(() => buildAdminSetupSteps(props.stats, cfg));
const complete = computed(() => isAdminSetupComplete(steps.value));
const doneCount = computed(() => steps.value.filter((s) => s.done).length);
</script>

<template>
  <AdminPanel v-if="!complete" class="mrt-admin-setup" :title="adminStr(cfg, 'setupTitle')">
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
        <button
          v-if="!step.done"
          type="button"
          class="button button-small mrt-admin-setup__go"
          @click="router.push(step.route)"
        >
          {{ adminStr(cfg, 'setupGo') }}
        </button>
      </li>
    </ol>
  </AdminPanel>
</template>
