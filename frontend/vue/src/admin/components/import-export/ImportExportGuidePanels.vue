<script setup lang="ts">
import { AdminPanel } from '../ui';
import type { AdminImportExportGuide } from '../../types';

defineProps<{
  guide: AdminImportExportGuide;
}>();
</script>

<template>
  <AdminPanel :title="guide.buildTitle">
    <ol class="mrt-admin-help-steps">
      <li v-for="(step, i) in guide.buildSteps" :key="i">{{ step }}</li>
    </ol>
  </AdminPanel>

  <AdminPanel :title="guide.packageTitle">
    <p class="description">{{ guide.packageHint }}</p>
    <table class="widefat striped mrt-admin-import-files">
      <thead>
        <tr>
          <th>{{ guide.colFile }}</th>
          <th>{{ guide.colRequired }}</th>
          <th>{{ guide.colDescription }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in guide.packageFiles" :key="row.file">
          <td><code>{{ row.file }}</code></td>
          <td>{{ row.required ? guide.requiredYes : guide.requiredNo }}</td>
          <td>{{ row.desc }}</td>
        </tr>
      </tbody>
    </table>
    <p class="description mrt-admin-import-docs">{{ guide.docsNote }}</p>
  </AdminPanel>

  <AdminPanel :title="guide.orderTitle">
    <p class="description">{{ guide.orderHint }}</p>
    <ol class="mrt-admin-help-steps">
      <li v-for="(step, i) in guide.orderSteps" :key="i"><code>{{ step }}</code></li>
    </ol>
  </AdminPanel>

  <AdminPanel :title="guide.keysTitle">
    <p>{{ guide.keysIntro }}</p>
    <ul class="mrt-admin-help-steps">
      <li v-for="(tip, i) in guide.keysTips" :key="i">{{ tip }}</li>
    </ul>
  </AdminPanel>

  <AdminPanel :title="guide.modesTitle">
    <p>{{ guide.modeMergeDetail }}</p>
    <p>{{ guide.modeOverrideDetail }}</p>
  </AdminPanel>

  <AdminPanel :title="guide.tipsTitle">
    <ul class="mrt-admin-help-steps">
      <li v-for="(tip, i) in guide.tips" :key="i">{{ tip }}</li>
    </ul>
  </AdminPanel>
</template>

<style scoped>
.mrt-admin-help-steps {
  margin: 0 0 0 1.5em;
}

.mrt-admin-import-files {
  margin-top: 12px;
}

.mrt-admin-import-files code {
  white-space: nowrap;
}

.mrt-admin-import-docs {
  margin-top: 12px;
  margin-bottom: 0;
}
</style>
