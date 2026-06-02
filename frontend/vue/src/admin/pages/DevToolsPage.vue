<script setup lang="ts">
import { ref } from 'vue';
import {
  devClearDatabase,
  devCreateDemoPage,
  devImportLennakatten,
  devSetupNavigation,
  devSyncTimetablePages,
} from '../api/adminRest';
import { AdminPanel, AdminStatusMessage, AdminToolList, MrtButton } from '../components/ui';
import { adminConfirm } from '../composables/adminConfirm';
import { adminConfig } from '../types';
import { adminStr } from '../utils/adminLabels';

const cfg = adminConfig();
const busy = ref('');
const message = ref('');
const error = ref('');

async function run(action: string, fn: () => Promise<unknown>) {
  if (busy.value) return;
  if (action === 'clear') {
    const ok = await adminConfirm({
      title: adminStr(cfg, 'devClearTitle'),
      message: adminStr(cfg, 'devClearMessage'),
      confirmLabel: adminStr(cfg, 'devClearConfirm'),
      danger: true,
    });
    if (!ok) {
      return;
    }
  }
  busy.value = action;
  error.value = '';
  message.value = '';
  try {
    await fn();
    const labels: Record<string, string> = {
      clear: adminStr(cfg, 'devClearSuccess'),
      import: adminStr(cfg, 'devImportSuccess'),
      demo: adminStr(cfg, 'devDemoSuccess'),
      nav: adminStr(cfg, 'devNavSuccess'),
      pages: adminStr(cfg, 'devPagesSuccess'),
    };
    message.value = labels[action] || adminStr(cfg, 'devDone');
  } catch (e) {
    error.value = e instanceof Error ? e.message : adminStr(cfg, 'genericError');
  } finally {
    busy.value = '';
  }
}
</script>

<template>
  <div>
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
