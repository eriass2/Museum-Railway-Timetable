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
    { to: '/timetables', label: 'Tidtabeller' },
    { to: '/stations-routes', label: 'Stationer & rutter' },
  ];
  if (cfg.canManage) {
    base.push(
      { to: '/train-types', label: 'Tågtyper' },
      { to: '/settings', label: 'Inställningar' },
      { to: '/prices', label: 'Priser' },
      { to: '/import-export', label: 'Import/export' },
    );
  }
  return base;
});

function isActive(path: string): boolean {
  if (path === '/timetables') {
    return route.path.startsWith('/timetables');
  }
  return route.path === path || route.path.startsWith(`${path}/`);
}
</script>

<template>
  <nav class="nav-tab-wrapper mrt-admin-nav">
    <a
      v-for="tab in tabs"
      :key="tab.to"
      href="#"
      class="nav-tab"
      :class="{ 'nav-tab-active': isActive(tab.to) }"
      @click.prevent="router.push(tab.to)"
    >
      {{ tab.label }}
    </a>
    <a
      v-if="cfg.canManage"
      class="nav-tab mrt-admin-nav__tools"
      :href="`${cfg.adminBase.replace('page=mrt_app', 'page=mrt_settings')}`"
    >
      Inställningar &amp; verktyg
    </a>
  </nav>
</template>
