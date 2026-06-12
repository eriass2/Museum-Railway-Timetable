<script setup lang="ts">
import { computed } from 'vue';
import AdminShortcodesGuide from '../components/AdminShortcodesGuide.vue';
import { AdminPanel, AdminTableScroll } from '../components/ui';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import { requireAdminHelp } from '../utils/adminHelpContent';
import { adminConfig } from '../types';

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
const help = computed(() => requireAdminHelp(cfg));
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

<style scoped>
.mrt-admin-page__lead {
  margin: -4px 0 20px;
  max-width: 52rem;
  color: #50575e;
  font-size: 14px;
  line-height: 1.5;
}

.mrt-admin-help-steps {
  margin: 0 0 0 1.5em;
}

.mrt-admin-shortcodes-quick {
  margin-top: 12px;
}

.mrt-admin-shortcodes-quick code {
  white-space: nowrap;
}
</style>
