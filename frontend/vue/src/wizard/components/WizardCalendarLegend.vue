<script setup lang="ts">
import { cfgStr } from '../utils/wizardLabels';
import type { MaybeRef } from 'vue';
import type { WizardCfg } from '../utils/wizardLabels';

defineProps<{ cfg: MaybeRef<WizardCfg> }>();
</script>

<template>
  <ul class="mrt-journey-wizard__legend">
    <li>
      <span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--ok" aria-hidden="true" />
      {{ cfgStr(cfg, 'legendOk', '') }}
    </li>
    <li>
      <span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--traffic" aria-hidden="true" />
      {{ cfgStr(cfg, 'legendTraffic', '') }}
    </li>
    <li>
      <span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--none" aria-hidden="true" />
      {{ cfgStr(cfg, 'legendNone', '') }}
    </li>
  </ul>
</template>
