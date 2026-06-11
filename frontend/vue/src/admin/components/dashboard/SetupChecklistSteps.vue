<script setup lang="ts">
import type { AdminSetupStep } from '../../utils/dashboard/adminSetupSteps';
import { adminStr } from '../../utils/adminLabels';
import { adminConfig } from '../../types';
import { MrtButton } from '../ui';

defineProps<{
  steps: AdminSetupStep[];
  complete: boolean;
}>();

defineEmits<{ go: [route: string] }>();

const cfg = adminConfig();
</script>

<template>
  <ol class="mrt-admin-setup__list">
    <li
      v-for="step in steps"
      :key="step.id"
      class="mrt-admin-setup__item"
      :class="{ 'mrt-admin-setup__item--done': complete || step.done }"
    >
      <span class="mrt-admin-setup__status" aria-hidden="true">
        {{ complete || step.done ? '✓' : '○' }}
      </span>
      <span class="mrt-admin-setup__label">{{ step.label }}</span>
      <MrtButton
        v-if="complete || !step.done"
        context="admin"
        variant="small"
        class="mrt-admin-setup__go"
        @click="$emit('go', step.route)"
      >
        {{ adminStr(cfg, 'setupGo') }}
      </MrtButton>
    </li>
  </ol>
</template>
