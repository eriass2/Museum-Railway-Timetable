<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminConfig } from '../types';

const route = useRoute();
const router = useRouter();
const cfg = adminConfig();

const tabs = computed(() => {
  const base = [
    { to: '/dashboard', label: 'Översikt' },
    { to: '/stations-routes', label: 'Stationer & rutter' },
    { to: '/timetables', label: 'Tidtabeller' },
    { to: '/help', label: 'Hjälp' },
  ];
  if (cfg.canManage) {
    base.push(
      { to: '/train-types', label: 'Tågtyper' },
      { to: '/settings', label: 'Inställningar' },
      { to: '/prices', label: 'Priser' },
      { to: '/import-export', label: 'Import/export' },
    );
  }
  if (cfg.canManage && cfg.isDevMode) {
    base.push({ to: '/dev-tools', label: 'Dev' });
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
  <aside class="mrt-admin-shell__nav" aria-label="Tidtabell admin">
    <p class="mrt-admin-shell__brand">Tidtabell</p>
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
      <a :href="cfg.componentDemoAdminUrl" class="mrt-admin-shell__tools-link">Komponentdemo</a>
    </p>
  </aside>
</template>
