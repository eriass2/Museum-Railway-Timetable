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

<style scoped>
.mrt-admin-setup__list {
  margin: 0 0 0 1.5em;
  padding: 0;
}

.mrt-admin-setup__item {
  margin-bottom: 8px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}

.mrt-admin-setup__item--done {
  color: #50575e;
}

.mrt-admin-setup__item--done .mrt-admin-setup__label {
  text-decoration: line-through;
}

.mrt-admin-setup__status {
  font-weight: 600;
  min-width: 1.25em;
}

.mrt-admin-setup__go {
  margin-left: auto;
}
</style>
