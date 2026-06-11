<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminStr } from '../utils/adminLabels';
import { buildAdminNavTabs, isAdminNavTabActive } from '../utils/adminNavTabs';
import { adminConfig } from '../types';

const route = useRoute();
const router = useRouter();
const cfg = adminConfig();

const tabs = computed(() => buildAdminNavTabs(cfg));

function isActive(path: string): boolean {
  return isAdminNavTabActive(path, route.path);
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
