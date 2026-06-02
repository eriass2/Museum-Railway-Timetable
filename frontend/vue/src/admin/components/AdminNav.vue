<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminStr } from '../utils/adminLabels';
import { adminConfig } from '../types';

const route = useRoute();
const router = useRouter();
const cfg = adminConfig();

const tabs = computed(() => {
  const base = [
    { to: '/dashboard', label: adminStr(cfg, 'navOverview', 'Översikt') },
    { to: '/stations-routes', label: adminStr(cfg, 'navStationsRoutes', 'Stationer & rutter') },
    { to: '/timetables', label: adminStr(cfg, 'navTimetables', 'Tidtabeller') },
    { to: '/help', label: adminStr(cfg, 'navHelp', 'Hjälp') },
  ];
  if (cfg.canManage) {
    base.push(
      { to: '/train-types', label: adminStr(cfg, 'navTrainTypes', 'Tågtyper') },
      { to: '/settings', label: adminStr(cfg, 'navSettings', 'Inställningar') },
      { to: '/prices', label: adminStr(cfg, 'navPrices', 'Priser') },
      { to: '/import-export', label: adminStr(cfg, 'navImportExport', 'Import/export') },
    );
  }
  if (cfg.canManage && cfg.isDevMode) {
    base.push({ to: '/dev-tools', label: adminStr(cfg, 'navDev', 'Dev') });
  }
  return base;
});

function isActive(path: string): boolean {
  if (path === '/timetables') {
    return route.path.startsWith('/timetables');
  }
  return route.path === path || route.path.startsWith(`${path}/`);
}

function navigate(path: string) {
  void router.push(path);
}
</script>

<template>
  <aside class="mrt-admin-shell__nav" :aria-label="adminStr(cfg, 'navAria', 'Tidtabell admin')">
    <p class="mrt-admin-shell__brand">{{ adminStr(cfg, 'navBrand', 'Tidtabell') }}</p>
    <nav class="mrt-admin-shell__menu">
      <a
        v-for="tab in tabs"
        :key="tab.to"
        href="#"
        class="mrt-admin-shell__link"
        :class="{ 'mrt-admin-shell__link--active': isActive(tab.to) }"
        @click.prevent="navigate(tab.to)"
      >
        {{ tab.label }}
      </a>
    </nav>
    <p v-if="cfg.componentDemoAdminUrl" class="mrt-admin-shell__tools">
      <a :href="cfg.componentDemoAdminUrl" class="mrt-admin-shell__tools-link">
        {{ adminStr(cfg, 'navComponentDemo', 'Komponentdemo') }}
      </a>
    </p>
  </aside>
</template>
