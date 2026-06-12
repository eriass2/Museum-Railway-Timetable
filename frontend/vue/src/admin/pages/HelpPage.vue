<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { AdminPanel, AdminTableScroll, MrtButton } from '../components/ui';
import { useHelpPage } from '../composables/useHelpPage';
import { useHelpSectionScroll } from '../composables/useHelpSectionScroll';
import { useMobileAdmin } from '../composables/mobile/useMobileAdmin';

const router = useRouter();
const route = useRoute();
const { isMobile } = useMobileAdmin();
const { cfg, help, visibleAdminSections, faqAnswer } = useHelpPage();
const { scrollToHelpSection } = useHelpSectionScroll(route);

const tocLinks = computed(() => [
  { id: 'what', label: help.value.panelWhat },
  ...(cfg.canManage ? [{ id: 'price-zones', label: help.value.panelPriceZones }] : []),
  { id: 'admin', label: help.value.panelAdmin },
  { id: 'workflow', label: help.value.panelWorkflow },
  { id: 'operations', label: help.value.panelOperations },
  { id: 'faq', label: help.value.panelFaq },
]);

function openShortcodesGuide() {
  void router.push('/shortcodes');
}
</script>

<template>
  <div class="mrt-admin-page" :class="{ 'mrt-admin-page--mobile': isMobile }">
    <h1>{{ help.title }}</h1>

    <nav class="mrt-admin-help-toc" :aria-label="help.tocTitle">
      <p class="mrt-admin-help-toc__title">{{ help.tocTitle }}</p>
      <ul>
        <li v-for="link in tocLinks" :key="link.id">
          <a :href="`#mrt-help-${link.id}`" @click.prevent="scrollToHelpSection(link.id)">
            {{ link.label }}
          </a>
        </li>
      </ul>
    </nav>

    <AdminPanel :id="'mrt-help-what'" :title="help.panelWhat">
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

    <AdminPanel :id="'mrt-help-admin'" :title="help.panelAdmin">
      <p class="description">{{ help.panelAdminHint }}</p>
      <dl class="mrt-admin-faq">
        <template v-for="section in visibleAdminSections" :key="section.title">
          <dt>{{ section.title }}</dt>
          <dd>{{ section.body }}</dd>
        </template>
      </dl>
    </AdminPanel>

    <AdminPanel :id="'mrt-help-workflow'" :title="help.panelWorkflow">
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in help.workflowSteps" :key="i">{{ step }}</li>
      </ol>
    </AdminPanel>

    <AdminPanel :id="'mrt-help-operations'" :title="help.panelOperations">
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

    <AdminPanel :id="'mrt-help-faq'" :title="help.panelFaq">
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

<style scoped>
.mrt-admin-help-toc {
  margin: 0 0 16px;
  padding: 12px 16px;
  background: #f6f7f7;
  border-left: 4px solid #2271b1;
}

.mrt-admin-help-toc__title {
  margin: 0 0 8px;
  font-weight: 600;
}

.mrt-admin-help-toc ul {
  margin: 0;
  padding-left: 1.25em;
}

.mrt-admin-help-steps {
  margin: 0 0 0 1.5em;
}

.mrt-admin-faq {
  margin: 0;
}

.mrt-admin-faq dt {
  margin-top: 12px;
  font-weight: 600;
}

.mrt-admin-faq dt:first-child {
  margin-top: 0;
}

.mrt-admin-faq dd {
  margin: 4px 0 0;
  color: #50575e;
}

.mrt-admin-help-table {
  margin-top: 12px;
}
</style>
