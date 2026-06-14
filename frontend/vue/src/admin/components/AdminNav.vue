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
  flex: 0 0 var(--mrt-admin-nav-width);
  position: sticky;
  top: var(--mrt-admin-nav-sticky-top);
  padding: var(--mrt-admin-nav-padding-block) 0;
  background: var(--mrt-admin-surface-bg);
  border: 1px solid var(--mrt-admin-panel-border);
  box-shadow: var(--mrt-admin-panel-shadow);
}

.mrt-admin-shell__brand {
  margin: 0 0 var(--mrt-admin-gap-sm);
  padding: 0 var(--mrt-admin-nav-padding-inline) var(--mrt-admin-nav-brand-padding-block-end);
  border-bottom: 1px solid var(--mrt-admin-border-subtle);
  font-size: var(--mrt-admin-font-brand);
  font-weight: 700;
  line-height: 1.3;
  color: var(--mrt-admin-text);
}

.mrt-admin-shell__menu {
  display: flex;
  flex-direction: column;
  gap: var(--mrt-admin-gap-xs);
  padding: var(--mrt-spacing-xs) 0;
}

.mrt-admin-shell__link {
  display: block;
  padding: var(--mrt-admin-nav-link-padding);
  color: var(--mrt-admin-link-color);
  text-decoration: none;
  font-size: var(--mrt-admin-font-sm);
  line-height: 1.35;
  border-left: var(--mrt-admin-nav-active-border) solid transparent;
}

.mrt-admin-shell__link:hover,
.mrt-admin-shell__link:focus {
  color: var(--mrt-admin-accent-border);
  background: var(--mrt-admin-surface-muted-bg);
}

.mrt-admin-shell__link--active {
  color: var(--mrt-admin-text);
  font-weight: 600;
  background: var(--mrt-admin-accent-bg);
  border-left-color: var(--mrt-admin-accent-border);
}

.mrt-admin-shell__tools {
  margin: var(--mrt-admin-gap-md) 0 0;
  padding: var(--mrt-admin-gap-md) var(--mrt-admin-nav-padding-inline) 0;
  border-top: 1px solid var(--mrt-admin-border-subtle);
  font-size: var(--mrt-admin-font-xs);
}

.mrt-admin-shell__tools-link {
  color: var(--mrt-admin-text-muted);
  text-decoration: none;
}

.mrt-admin-shell__tools-link:hover,
.mrt-admin-shell__tools-link:focus {
  color: var(--mrt-admin-accent-border);
}

@media (max-width: 782px) {
  .mrt-admin-shell__nav {
    position: static;
    flex: none;
    width: 100%;
    padding: var(--mrt-admin-gap-sm) 0;
  }

  .mrt-admin-shell__menu {
    flex-direction: row;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 0;
    padding: 0 var(--mrt-admin-gap-sm) var(--mrt-spacing-xs);
    -webkit-overflow-scrolling: touch;
  }

  .mrt-admin-shell__link {
    flex: 0 0 auto;
    padding: var(--mrt-admin-nav-link-padding-mobile);
    border-left: 0;
    border-bottom: var(--mrt-admin-nav-active-border) solid transparent;
    white-space: nowrap;
  }

  .mrt-admin-shell__link--active {
    border-left-color: transparent;
    border-bottom-color: var(--mrt-admin-accent-border);
  }
}
</style>
