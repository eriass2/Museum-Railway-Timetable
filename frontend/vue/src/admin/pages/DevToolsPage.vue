<script setup lang="ts">
import { ref } from 'vue';
import {
  devClearDatabase,
  devCreateDemoPage,
  devImportLennakatten,
  devSetupNavigation,
  devSyncTimetablePages,
} from '../api/adminRest';
import { AdminPanel, AdminStatusMessage, AdminToolList } from '../components/ui';
import { adminConfirm } from '../composables/adminConfirm';
import { adminConfig } from '../types';

const cfg = adminConfig();
const busy = ref('');
const message = ref('');
const error = ref('');

async function run(action: string, fn: () => Promise<unknown>) {
  if (busy.value) return;
  if (action === 'clear') {
    const ok = await adminConfirm({
      title: 'Radera all plugin-data',
      message:
        'Alla stationer, rutter, tidtabeller, turer och inställningar tas bort. Detta går inte att ångra.',
      confirmLabel: 'Radera allt',
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
      clear: 'All plugin-data har raderats.',
      import: 'Lennakatten-demo har importerats.',
      demo: 'Demosida skapad eller uppdaterad.',
      nav: 'Utvecklingsmeny uppdaterad.',
      pages: 'Tidtabellssidor skapade eller uppdaterade.',
    };
    message.value = labels[action] || 'Klart.';
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Fel';
  } finally {
    busy.value = '';
  }
}
</script>

<template>
  <div>
    <h1>Utvecklingsverktyg</h1>
    <p v-if="!cfg.isDevMode" class="notice notice-warning">
      Endast tillgängligt när WP_DEBUG eller MRT_DEVELOPMENT är aktivt.
    </p>
    <template v-else>
      <p class="description">
        Reset, import och demosidor för lokal QA. Visas inte på produktion.
      </p>
      <AdminStatusMessage :message="message" />
      <AdminStatusMessage v-if="error" :message="error" type="error" />
      <AdminPanel>
        <AdminToolList>
          <button
            type="button"
            class="button button-link-delete"
            :disabled="!!busy"
            @click="run('clear', devClearDatabase)"
          >
            Rensa plugin-databas
          </button>
          <button
            type="button"
            class="button button-primary"
            :disabled="!!busy"
            @click="run('import', devImportLennakatten)"
          >
            Importera Lennakatten-demo
          </button>
          <button type="button" class="button" :disabled="!!busy" @click="run('demo', devCreateDemoPage)">
            Skapa demosida
          </button>
          <button type="button" class="button" :disabled="!!busy" @click="run('nav', devSetupNavigation)">
            Sätt upp utvecklingsmeny
          </button>
          <button type="button" class="button" :disabled="!!busy" @click="run('pages', devSyncTimetablePages)">
            Skapa/uppdatera tidtabellssidor
          </button>
        </AdminToolList>
        <p v-if="cfg.componentDemoAdminUrl" class="description mrt-mt-sm">
          <a :href="cfg.componentDemoAdminUrl">Komponentdemosida (PHP-admin)</a>
        </p>
      </AdminPanel>
    </template>
  </div>
</template>
