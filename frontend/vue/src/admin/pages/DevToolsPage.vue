<script setup lang="ts">
import { AdminPanel, AdminStatusMessage, AdminToolList, MrtButton } from '../components/ui';
import { useDevToolsPage } from '../composables/useDevToolsPage';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';
import { adminStr } from '../utils/adminLabels';

const { isMobile } = useMobileAdmin();
const {
  cfg,
  busy,
  message,
  error,
  run,
  devClearDatabase,
  devImportLennakatten,
  devCreateDemoPage,
  devSetupNavigation,
  devSyncTimetablePages,
} = useDevToolsPage();
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ adminStr(cfg, 'devTitle') }}</h1>
    <p v-if="!cfg.isDevMode" class="notice notice-warning">
      {{ adminStr(cfg, 'devNotAvailable') }}
    </p>
    <template v-else>
      <p class="description">
        {{ adminStr(cfg, 'devDescription') }}
      </p>
      <AdminStatusMessage :message="message" />
      <AdminStatusMessage v-if="error" :message="error" type="error" />
      <AdminPanel>
        <AdminToolList>
          <MrtButton
            context="admin"
            variant="link-delete"
            :disabled="!!busy"
            @click="run('clear', devClearDatabase)"
          >
            {{ adminStr(cfg, 'devClearButton') }}
          </MrtButton>
          <MrtButton
            context="admin"
            variant="primary"
            :disabled="!!busy"
            @click="run('import', devImportLennakatten)"
          >
            {{ adminStr(cfg, 'devImportButton') }}
          </MrtButton>
          <MrtButton context="admin" variant="secondary" :disabled="!!busy" @click="run('demo', devCreateDemoPage)">
            {{ adminStr(cfg, 'devDemoButton') }}
          </MrtButton>
          <MrtButton context="admin" variant="secondary" :disabled="!!busy" @click="run('nav', devSetupNavigation)">
            {{ adminStr(cfg, 'devNavButton') }}
          </MrtButton>
          <MrtButton context="admin" variant="secondary" :disabled="!!busy" @click="run('pages', devSyncTimetablePages)">
            {{ adminStr(cfg, 'devPagesButton') }}
          </MrtButton>
        </AdminToolList>
        <p v-if="cfg.componentDemoAdminUrl" class="description mrt-mt-sm">
          <a :href="cfg.componentDemoAdminUrl">{{ adminStr(cfg, 'devComponentDemoLink') }}</a>
        </p>
      </AdminPanel>
    </template>
  </div>
</template>
