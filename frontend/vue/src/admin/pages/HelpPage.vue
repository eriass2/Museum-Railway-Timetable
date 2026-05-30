<script setup lang="ts">
import AdminNav from '../components/AdminNav.vue';
import { adminConfig } from '../types';

const cfg = adminConfig();

const workflowSteps = [
  'Skapa stationer under Stationer & rutter',
  'Skapa rutter och lägg stationer i ordning',
  'Skapa en tidtabell och lägg till trafikdagar',
  'Lägg till turer (koppla rutt och destination)',
  'Fyll i stopptider i editorn (rutnät eller tabellvy)',
];

const faqItems = [
  {
    q: 'Var börjar jag?',
    a: 'Följ ordningen stationer → rutter → tidtabell → turer → stopptider. På mobil räcker det ofta att ändra avvikelser och avgångstider via Översikt eller tidtabellseditorn.',
  },
  {
    q: 'Vad betyder varningarna på översikten?',
    a: 'De är dataproblem (t.ex. tidtabell utan datum, tur utan stopptider). Klicka på varningen för att gå till rätt sida och åtgärda.',
  },
  {
    q: 'Hur ställer jag in trafik för idag?',
    a: 'På Översikt: Inställ trafik idag sätter meddelandet «Inställd» på alla dagens turer. Du kan också ändra en enskild avgångstid eller avvikelse i tidtabellseditorn.',
  },
  {
    q: 'Kan jag radera stationer och rutter?',
    a: 'Ja, om de inte används. En station som ingår i en rutt eller har stopptider kan inte raderas förrän kopplingen tagits bort. Rutter med turer kan inte raderas.',
  },
  {
    q: 'Vad är skillnaden mellan admin och redaktör?',
    a: cfg.canManage
      ? 'Du har full behörighet: grunddata, priser, import och inställningar.'
      : 'Som redaktör kan du läsa allt och ändra avvikelser samt snabb avgångstid. Grunddata (stationer, tidtabeller, priser m.m.) kräver administratör.',
  },
  {
    q: 'Hur importerar jag data?',
    a: cfg.canManage
      ? 'Gå till Import / export. CSV zip kan slås samman med befintlig data eller ersätta den. I utvecklingsläge finns även Lennakatten-demo under Utvecklingsverktyg.'
      : 'Import och export kräver administratörsbehörighet. Be en administratör importera eller exportera åt dig.',
  },
  {
    q: 'Var syns tidtabellen på webbplatsen?',
    a: 'Via shortcodes (t.ex. månadsvy och översikt) på sidor som skapats manuellt eller synkats i utvecklingsläge. Länken Visa webbplats på översikten öppnar startsidan.',
  },
  {
    q: 'Fungerar admin på mobil?',
    a: 'Ja för drift: inställ trafik, avvikelser och snabb avgångstid. Full redigering (datum, turer, stopptidsrutnät) görs enklast på desktop.',
  },
];
</script>

<template>
  <div class="mrt-admin-page">
    <h1>Hjälp</h1>
    <AdminNav />

    <div class="mrt-admin-panel">
      <h2>Arbetsflöde</h2>
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in workflowSteps" :key="i">{{ step }}</li>
      </ol>
    </div>

    <div class="mrt-admin-panel">
      <h2>Vanliga frågor</h2>
      <dl class="mrt-admin-faq">
        <template v-for="(item, i) in faqItems" :key="i">
          <dt>{{ item.q }}</dt>
          <dd>{{ item.a }}</dd>
        </template>
      </dl>
    </div>

    <div class="mrt-admin-panel">
      <h2>Mer information</h2>
      <p>
        Full steg-för-steg-guide finns i plugin-dokumentationen:
        <code>docs/ADMIN_WORKFLOW.md</code> i projektets källkod.
      </p>
      <p class="description">
        CSV-format, shortcodes och utvecklingsverktyg beskrivs i övriga filer under
        <code>docs/</code>.
      </p>
    </div>
  </div>
</template>
