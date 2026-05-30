<script setup lang="ts">
import { ref } from 'vue';
import {
  devClearDatabase,
  devCreateDemoPage,
  devImportLennakatten,
  devSetupNavigation,
} from '../api/adminRest';
import AdminNav from '../components/AdminNav.vue';
import { adminConfig } from '../types';

const cfg = adminConfig();
const busy = ref('');
const message = ref('');
const error = ref('');

async function run(action: string, fn: () => Promise<unknown>) {
  if (busy.value) return;
  if (action === 'clear' && !window.confirm('Radera ALL plugin-data? Detta går inte att ångra.')) {
    return;
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
    <AdminNav />
    <p v-if="!cfg.isDevMode" class="notice notice-warning">
      Endast tillgängligt när WP_DEBUG eller MRT_DEVELOPMENT är aktivt.
    </p>
    <template v-else>
      <p class="description">
        Reset, import och demosidor för lokal QA. Visas inte på produktion.
      </p>
      <p v-if="message" class="notice notice-success">{{ message }}</p>
      <p v-if="error" class="notice notice-error">{{ error }}</p>
      <div class="mrt-admin-panel">
        <p>
          <button
            type="button"
            class="button button-link-delete"
            :disabled="!!busy"
            @click="run('clear', devClearDatabase)"
          >
            Rensa plugin-databas
          </button>
        </p>
        <p>
          <button
            type="button"
            class="button button-primary"
            :disabled="!!busy"
            @click="run('import', devImportLennakatten)"
          >
            Importera Lennakatten-demo
          </button>
        </p>
        <p>
          <button
            type="button"
            class="button"
            :disabled="!!busy"
            @click="run('demo', devCreateDemoPage)"
          >
            Skapa demosida
          </button>
        </p>
        <p>
          <button
            type="button"
            class="button"
            :disabled="!!busy"
            @click="run('nav', devSetupNavigation)"
          >
            Sätt upp utvecklingsmeny
          </button>
        </p>
      </div>
    </template>
  </div>
</template>
