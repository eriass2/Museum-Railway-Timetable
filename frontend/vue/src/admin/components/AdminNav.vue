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

<style scoped>
.mrt-admin-shell__nav {
  flex: 0 0 11.5rem;
  position: sticky;
  top: 32px;
  padding: 12px 0;
  background: #fff;
  border: 1px solid #c3c4c7;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
}

.mrt-admin-shell__brand {
  margin: 0 0 8px;
  padding: 0 14px 10px;
  border-bottom: 1px solid #dcdcde;
  font-size: 0.95rem;
  font-weight: 700;
  line-height: 1.3;
  color: #1d2327;
}

.mrt-admin-shell__menu {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 4px 0;
}

.mrt-admin-shell__link {
  display: block;
  padding: 8px 14px;
  color: #2c3338;
  text-decoration: none;
  font-size: 13px;
  line-height: 1.35;
  border-left: 3px solid transparent;
}

.mrt-admin-shell__link:hover,
.mrt-admin-shell__link:focus {
  color: #2271b1;
  background: #f6f7f7;
}

.mrt-admin-shell__link--active {
  color: #1d2327;
  font-weight: 600;
  background: #f0f6fc;
  border-left-color: #2271b1;
}

.mrt-admin-shell__tools {
  margin: 10px 0 0;
  padding: 10px 14px 0;
  border-top: 1px solid #dcdcde;
  font-size: 12px;
}

.mrt-admin-shell__tools-link {
  color: #50575e;
  text-decoration: none;
}

.mrt-admin-shell__tools-link:hover,
.mrt-admin-shell__tools-link:focus {
  color: #2271b1;
}

@media (max-width: 782px) {
  .mrt-admin-shell__nav {
    position: static;
    flex: none;
    width: 100%;
    padding: 8px 0;
  }

  .mrt-admin-shell__menu {
    flex-direction: row;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 0;
    padding: 0 8px 4px;
    -webkit-overflow-scrolling: touch;
  }

  .mrt-admin-shell__link {
    flex: 0 0 auto;
    padding: 8px 12px;
    border-left: 0;
    border-bottom: 3px solid transparent;
    white-space: nowrap;
  }

  .mrt-admin-shell__link--active {
    border-left-color: transparent;
    border-bottom-color: #2271b1;
  }
}
</style>
