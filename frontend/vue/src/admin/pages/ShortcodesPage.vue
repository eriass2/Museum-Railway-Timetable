<script setup lang="ts">
import { computed } from 'vue';
import { adminConfig } from '../types';
import AdminShortcodesGuide from '../components/AdminShortcodesGuide.vue';
import { AdminPanel } from '../components/ui';

const cfg = adminConfig();
const help = computed(() => {
  if (!cfg.help) {
    throw new Error('mrtAdminVue.help config missing');
  }
  return cfg.help;
});
</script>

<template>
  <div class="mrt-admin-page">
    <h1>{{ help.shortcodesPageTitle }}</h1>
    <p class="mrt-admin-page__lead">{{ help.shortcodesPageIntro }}</p>

    <AdminPanel :title="help.shortcodesHowToTitle">
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in help.shortcodesHowToSteps" :key="i">{{ step }}</li>
      </ol>
    </AdminPanel>

    <AdminPanel :title="help.shortcodesQuickRefTitle">
      <p class="description">{{ help.shortcodesQuickRefHint }}</p>
      <table class="widefat striped mrt-admin-shortcodes-quick">
        <thead>
          <tr>
            <th>{{ help.shortcodesColShortcode }}</th>
            <th>{{ help.colDescription }}</th>
            <th>{{ help.shortcodesColUse }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sc in help.shortcodes" :key="sc.tag">
            <td><code>[{{ sc.tag }}]</code></td>
            <td>{{ sc.title }}</td>
            <td>{{ sc.summary }}</td>
          </tr>
        </tbody>
      </table>
    </AdminPanel>

    <AdminPanel :title="help.shortcodesSetupTitle">
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in help.shortcodesSetupSteps" :key="i">{{ step }}</li>
      </ol>
    </AdminPanel>

    <AdminShortcodesGuide :help="help" :show-dev-hint="cfg.isDevMode" />

    <AdminPanel :title="help.shortcodesWidgetTitle">
      <p>{{ help.shortcodesWidgetNote }}</p>
    </AdminPanel>
  </div>
</template>
