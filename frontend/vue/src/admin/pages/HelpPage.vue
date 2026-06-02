<script setup lang="ts">
import { computed } from 'vue';
import { adminConfig } from '../types';
import { AdminPanel } from '../components/ui';

const cfg = adminConfig();
const help = computed(() => {
  if (!cfg.help) {
    throw new Error('mrtAdminVue.help config missing');
  }
  return cfg.help;
});

const visibleAdminSections = computed(() =>
  help.value.adminSections.filter((section) => {
    if (section.adminOnly && !cfg.canManage) return false;
    if (section.devOnly && !cfg.isDevMode) return false;
    return true;
  }),
);

function faqAnswer(item: (typeof help.value.faq)[number]): string {
  if (item.aEditor && !cfg.canManage) {
    return item.aEditor;
  }
  return item.a;
}
</script>

<template>
  <div class="mrt-admin-page">
    <h1>{{ help.title }}</h1>

    <AdminPanel :title="help.panelWhat">
      <p>{{ help.intro }}</p>
      <table class="widefat striped mrt-admin-help-table">
        <thead>
          <tr>
            <th>{{ help.colPart }}</th>
            <th>{{ help.colDescription }}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ help.partAdmin }}</td>
            <td>{{ help.partAdminDesc }}</td>
          </tr>
          <tr>
            <td>{{ help.partPublic }}</td>
            <td>{{ help.partPublicDesc }}</td>
          </tr>
        </tbody>
      </table>
    </AdminPanel>

    <AdminPanel :title="help.panelAdmin">
      <p class="description">{{ help.panelAdminHint }}</p>
      <dl class="mrt-admin-faq">
        <template v-for="section in visibleAdminSections" :key="section.title">
          <dt>{{ section.title }}</dt>
          <dd>{{ section.body }}</dd>
        </template>
      </dl>
    </AdminPanel>

    <AdminPanel :title="help.panelWorkflow">
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in help.workflowSteps" :key="i">{{ step }}</li>
      </ol>
    </AdminPanel>

    <AdminPanel :title="help.panelOperations">
      <ul class="mrt-admin-help-steps">
        <li v-for="item in help.operations" :key="item.title">
          <strong>{{ item.title }}</strong> {{ item.body }}
        </li>
      </ul>
      <p class="description">{{ help.operationsNote }}</p>
    </AdminPanel>

    <AdminPanel :title="help.panelShortcodes">
      <p>{{ help.shortcodesIntro }}</p>
      <p v-if="cfg.isDevMode" class="description">{{ help.shortcodesDevHint }}</p>

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

    <AdminPanel :title="help.panelFaq">
      <dl class="mrt-admin-faq">
        <template v-for="(item, i) in help.faq" :key="i">
          <dt>{{ item.q }}</dt>
          <dd>{{ faqAnswer(item) }}</dd>
        </template>
      </dl>
    </AdminPanel>

    <AdminPanel :title="help.panelMore">
      <p>{{ help.moreInfoBody }}</p>
      <p class="description">{{ help.moreInfoDocs }}</p>
    </AdminPanel>
  </div>
</template>
