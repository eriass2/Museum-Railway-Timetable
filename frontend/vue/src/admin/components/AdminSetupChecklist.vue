<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  buildAdminSetupSteps,
  isAdminSetupComplete,
} from '../utils/adminSetupSteps';

const props = defineProps<{
  stats: Record<string, number>;
}>();

const router = useRouter();
const steps = computed(() => buildAdminSetupSteps(props.stats));
const complete = computed(() => isAdminSetupComplete(steps.value));
const doneCount = computed(() => steps.value.filter((s) => s.done).length);
</script>

<template>
  <div v-if="!complete" class="mrt-admin-panel mrt-admin-setup">
    <h2>Kom igång</h2>
    <p class="description">
      {{ doneCount }} av {{ steps.length }} steg klara — följ ordningen nedan innan du publicerar
      tidtabeller på webbplatsen.
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
          Gå till
        </button>
      </li>
    </ol>
  </div>
</template>
