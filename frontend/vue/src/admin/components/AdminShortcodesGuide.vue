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
