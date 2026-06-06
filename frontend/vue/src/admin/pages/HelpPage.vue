<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { adminConfig } from '../types';
import { AdminPanel, MrtButton } from '../components/ui';

const router = useRouter();

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

function openShortcodesGuide() {
  void router.push('/shortcodes');
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
      <p>{{ help.helpLinkToShortcodes }}</p>
      <MrtButton context="admin" variant="secondary" @click="openShortcodesGuide">
        {{ help.shortcodesPageTitle }}
      </MrtButton>
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
