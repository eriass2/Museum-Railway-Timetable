<script setup lang="ts">
import { computed } from 'vue';
import { adminConfig } from '../types';
import AdminShortcodesGuide from '../components/AdminShortcodesGuide.vue';
import { AdminPanel, AdminTableScroll } from '../components/ui';
import { useMobileAdmin } from '../composables/useMobileAdmin';

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const help = computed(() => {
  if (!cfg.help) {
    throw new Error('mrtAdminVue.help config missing');
  }
  return cfg.help;
});
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ help.shortcodesPageTitle }}</h1>
    <p class="mrt-admin-page__lead">{{ help.shortcodesPageIntro }}</p>

    <AdminPanel :title="help.shortcodesHowToTitle">
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in help.shortcodesHowToSteps" :key="i">{{ step }}</li>
      </ol>
    </AdminPanel>

    <AdminPanel :title="help.shortcodesQuickRefTitle">
      <p class="description">{{ help.shortcodesQuickRefHint }}</p>
      <AdminTableScroll>
        <table class="widefat striped mrt-admin-shortcodes-quick mrt-admin-responsive-table">
          <thead>
            <tr>
              <th>{{ help.shortcodesColShortcode }}</th>
              <th>{{ help.colDescription }}</th>
              <th>{{ help.shortcodesColUse }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sc in help.shortcodes" :key="sc.tag">
              <td :data-label="help.shortcodesColShortcode"><code>[{{ sc.tag }}]</code></td>
              <td :data-label="help.colDescription">{{ sc.title }}</td>
              <td :data-label="help.shortcodesColUse">{{ sc.summary }}</td>
            </tr>
          </tbody>
        </table>
      </AdminTableScroll>
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
