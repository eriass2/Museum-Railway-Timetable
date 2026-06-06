<script setup lang="ts">
import { computed, nextTick, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminConfig } from '../types';
import { AdminPanel, AdminTableScroll, MrtButton } from '../components/ui';
import { useMobileAdmin } from '../composables/useMobileAdmin';

const router = useRouter();
const route = useRoute();

const cfg = adminConfig();
const { isMobile } = useMobileAdmin();
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

function scrollToHelpSection(sectionId: string | undefined) {
  if (!sectionId) {
    return;
  }
  void nextTick(() => {
    document.getElementById(`mrt-help-${sectionId}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}

onMounted(() => {
  scrollToHelpSection(typeof route.query.section === 'string' ? route.query.section : undefined);
});

watch(
  () => route.query.section,
  (section) => {
    scrollToHelpSection(typeof section === 'string' ? section : undefined);
  },
);
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ help.title }}</h1>

    <AdminPanel :title="help.panelWhat">
      <p>{{ help.intro }}</p>
      <AdminTableScroll>
        <table class="widefat striped mrt-admin-help-table mrt-admin-responsive-table">
          <thead>
            <tr>
              <th>{{ help.colPart }}</th>
              <th>{{ help.colDescription }}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td :data-label="help.colPart">{{ help.partAdmin }}</td>
              <td :data-label="help.colDescription">{{ help.partAdminDesc }}</td>
            </tr>
            <tr>
              <td :data-label="help.colPart">{{ help.partPublic }}</td>
              <td :data-label="help.colDescription">{{ help.partPublicDesc }}</td>
            </tr>
          </tbody>
        </table>
      </AdminTableScroll>
    </AdminPanel>

    <AdminPanel
      v-if="cfg.canManage"
      :id="'mrt-help-price-zones'"
      :title="help.panelPriceZones"
    >
      <p>{{ help.priceZonesIntro }}</p>
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in help.priceZonesSteps" :key="`price-zone-${i}`">{{ step }}</li>
      </ol>
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
