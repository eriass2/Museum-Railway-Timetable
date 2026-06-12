<script setup lang="ts">
import type { AdminHelpContent } from '../types';
import { AdminPanel } from './ui';

defineProps<{
  help: AdminHelpContent;
  showDevHint?: boolean;
}>();
</script>

<template>
  <AdminPanel :title="help.panelShortcodes">
    <p>{{ help.shortcodesIntro }}</p>
    <p v-if="showDevHint" class="description">{{ help.shortcodesDevHint }}</p>

    <article
      v-for="sc in help.shortcodes"
      :key="sc.tag"
      class="mrt-admin-shortcode"
    >
      <h3>{{ sc.title }}</h3>
      <p><code>[{{ sc.tag }}]</code></p>
      <p>{{ sc.summary }}</p>
      <p class="mrt-admin-shortcode__example">
        <strong>{{ help.shortcodeExample }}</strong>
        <code>{{ sc.example }}</code>
      </p>
      <table v-if="sc.params.length" class="widefat striped mrt-admin-shortcode__params">
        <thead>
          <tr>
            <th>{{ help.paramName }}</th>
            <th>{{ help.colDescription }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="param in sc.params" :key="param.name">
            <td><code>{{ param.name }}</code></td>
            <td>{{ param.desc }}</td>
          </tr>
        </tbody>
      </table>
    </article>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-shortcode {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #dcdcde;
}

.mrt-admin-shortcode:first-of-type {
  margin-top: 12px;
  padding-top: 0;
  border-top: 0;
}

.mrt-admin-shortcode h3 {
  margin: 0 0 8px;
}

.mrt-admin-shortcode__example code {
  display: inline-block;
  margin-top: 4px;
  word-break: break-word;
}

.mrt-admin-shortcode__params {
  margin-top: 10px;
}

.mrt-admin-shortcode__params code {
  white-space: nowrap;
}
</style>
