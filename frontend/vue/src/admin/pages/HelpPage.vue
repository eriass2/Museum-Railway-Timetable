<script setup lang="ts">
import AdminNav from '../components/AdminNav.vue';
import { adminConfig } from '../types';

const cfg = adminConfig();

type HelpSection = { title: string; body: string; adminOnly?: boolean; devOnly?: boolean };

const pluginIntro =
  'Museum Railway Timetable hanterar tidtabellsdata i WordPress och visar den på webbplatsen via shortcodes. ' +
  'I admin skapar du stationer, rutter, tidtabeller och turer. Besökare kan se tidtabeller, kalender och söka resor med reseplaneraren.';

const adminSections: HelpSection[] = [
  {
    title: 'Översikt',
    body: 'Statistik, varningar om dataproblem, nästa trafikdag och snabbstart. På mobil: inställ trafik idag och snabb avgångstid när det finns trafik.',
  },
  {
    title: 'Stationer & rutter',
    body: 'Grunddata: stationer (namn, typ, koordinater, buss-suffix) och rutter med stationer i ordning. Rutten styr vilka hållplatser som finns i stopptidsrutnätet.',
  },
  {
    title: 'Tidtabeller',
    body: 'Skapa och redigera tidtabeller. I editorn: titel och typ (färg), trafikdagar, turer, stopptider (rutnät eller tabell), avvikelser och förhandsvisning som på webbplatsen.',
  },
  {
    title: 'Tågtyper',
    body: 'Kategorier med namn, slug och ikon — visas i tidtabellsöversikt och kan filtrera månadskalendern.',
    adminOnly: true,
  },
  {
    title: 'Inställningar',
    body: 'Aktivera/inaktivera plugin, intern anteckning och min/max väntetid vid byte i reseplaneraren.',
    adminOnly: true,
  },
  {
    title: 'Priser',
    body: 'Prismatris (biljettyp × kategori × zoner) som reseplaneraren använder i sammanfattningssteget.',
    adminOnly: true,
  },
  {
    title: 'Import / export',
    body: 'Säkerhetskopiera eller flytta all data som CSV i zip. Välj slå ihop eller ersätt vid import.',
    adminOnly: true,
  },
  {
    title: 'Utvecklingsverktyg',
    body: 'Demoimport, demosida, tidtabellssidor och databasrensning — endast i utvecklingsläge, syns inte på produktion.',
    adminOnly: true,
    devOnly: true,
  },
];

const visibleAdminSections = adminSections.filter((section) => {
  if (section.adminOnly && !cfg.canManage) return false;
  if (section.devOnly && !cfg.isDevMode) return false;
  return true;
});

const workflowSteps = [
  'Skapa stationer under Stationer & rutter',
  'Skapa rutter och lägg stationer i ordning',
  'Valfritt: skapa tågtyper med ikoner (Tågtyper)',
  'Skapa en tidtabell och lägg till trafikdagar',
  'Lägg till turer (koppla rutt och destination)',
  'Fyll i stopptider i editorn (rutnät eller tabellvy)',
  'Kontrollera förhandsvisningen — samma vy som på webbplatsen',
  'Publicera: lägg shortcodes på sidor (eller skapa sidor i utvecklingsläge)',
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
    a: 'Via shortcodes på WordPress-sidor — se avsnittet Shortcodes nedan. I utvecklingsläge kan du synka färdiga sidor under Utvecklingsverktyg.',
  },
  {
    q: 'Fungerar admin på mobil?',
    a: 'Ja för drift: inställ trafik, avvikelser och snabb avgångstid. Full redigering (datum, turer, stopptidsrutnät) görs enklast på desktop.',
  },
  {
    q: 'Vad gör tidtabellseditorns flikar?',
    a: 'Trafikdagar: vilka datum tidtabellen gäller. Turer: lägg till eller ta bort avgångar. Stopptider: tider per hållplats. Avvikelser: ändra tågtyp eller meddelande för ett visst datum. Förhandsvisning: hur det ser ut för besökare.',
  },
  {
    q: 'Hur fungerar priser och inställningar?',
    a: cfg.canManage
      ? 'Priser styr reseplanerarens prissammanfattning. Inställningar styr om pluginet är aktivt och hur lång väntetid som accepteras vid byte. Båda nås via menyn och kan exporteras med CSV.'
      : 'Priser och inställningar kräver administratörsbehörighet.',
  },
  {
    q: 'Kan jag radera en hel tidtabell?',
    a: 'Ja — i tidtabellslistan eller editorn. Alla turer och stopptider i tidtabellen tas bort. Publicerade WordPress-sidor med shortcode påverkas inte automatiskt; uppdatera eller ta bort sidor manuellt.',
  },
];

type ShortcodeParam = { name: string; desc: string };

type ShortcodeHelp = {
  tag: string;
  title: string;
  summary: string;
  example: string;
  params: ShortcodeParam[];
};

const shortcodes: ShortcodeHelp[] = [
  {
    tag: 'museum_timetable_index',
    title: 'Lista tidtabeller',
    summary:
      'Visar klickbara kort för alla publicerade tidtabeller med länkar till respektive tidtabellssida. Passar som startsida eller hubb.',
    example: '[museum_timetable_index show_dates="1" intro="1"]',
    params: [
      { name: 'show_dates', desc: 'Visa antal trafikdagar och datumspann (1/0, standard 1)' },
      { name: 'intro', desc: 'Visa kort introduktionstext (1/0, standard 1)' },
    ],
  },
  {
    tag: 'museum_timetable_overview',
    title: 'Tidtabellsöversikt',
    summary:
      'Visar hela tidtabellen som rutnät: turer grupperade per rutt och riktning, med tider, tågtyper och avvikelser för vald dag.',
    example: '[museum_timetable_overview timetable_id="123"]',
    params: [
      { name: 'timetable_id', desc: 'Tidtabellens ID (rekommenderat — syns i editorns URL)' },
      { name: 'timetable', desc: 'Alternativt: exakt titel på tidtabellen' },
    ],
  },
  {
    tag: 'museum_timetable_month',
    title: 'Månadskalender',
    summary:
      'Kalender som visar vilka dagar som har trafik. Besökare kan bläddra månad och se antal turer per dag.',
    example: '[museum_timetable_month month="2026-06" train_type="angtag"]',
    params: [
      { name: 'month', desc: 'Månad som YYYY-MM (standard: aktuell månad)' },
      { name: 'train_type', desc: 'Filtrera på tågtypens slug (se Tågtyper)' },
      { name: 'service', desc: 'Filtrera på exakt tur-titel (valfritt)' },
      { name: 'legend', desc: 'Visa förklaring (1/0, standard 1)' },
      { name: 'show_counts', desc: 'Visa antal turer per dag (1/0, standard 0). Siffran gäller alla linjer och riktningar.' },
      { name: 'start_monday', desc: 'Vecka börjar måndag (1/0, standard 1)' },
      { name: 'nav', desc: 'Länkar föregående/nästa månad (1/0, standard 1)' },
    ],
  },
  {
    tag: 'museum_journey_wizard',
    title: 'Reseplanerare',
    summary:
      'Flerstegsflöde: välj rutt, datum, utresa och ev. retur. Visar priser och anslutningar. Kräver JavaScript.',
    example:
      '[museum_journey_wizard ticket_url="https://example.com/biljetter" timetable_page_url="https://example.com/tidtabeller"]',
    params: [
      { name: 'ticket_url', desc: 'Reserverat (inaktiverat) — biljettknapp visas inte i nuvarande version' },
      { name: 'timetable_page_url', desc: 'Länk till tidtabellssida (visas under sök på steg 1)' },
      { name: 'embedded', desc: 'Kompakt layout inuti sidinnehåll (1/true)' },
      { name: 'timetable_id', desc: 'Legacy — inbäddad översikt under steg 1 (rekommenderas sällan)' },
      { name: 'timetable', desc: 'Samma som timetable_id men med tidtabellstitel' },
    ],
  },
];
</script>

<template>
  <div class="mrt-admin-page">
    <h1>Hjälp</h1>
    <AdminNav />

    <div class="mrt-admin-panel">
      <h2>Vad pluginet gör</h2>
      <p>{{ pluginIntro }}</p>
      <table class="widefat striped mrt-admin-help-table">
        <thead>
          <tr>
            <th>Del</th>
            <th>Beskrivning</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Administration</td>
            <td>Data under menyn Tidtabell — stationer, rutter, tidtabeller, drift och (för administratör) priser och import.</td>
          </tr>
          <tr>
            <td>Webbplats</td>
            <td>Shortcodes på WordPress-sidor: lista, tidtabellsöversikt, månadskalender och reseplanerare.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mrt-admin-panel">
      <h2>Administration</h2>
      <p class="description">Vad varje menyval gör i admin.</p>
      <dl class="mrt-admin-faq">
        <template v-for="section in visibleAdminSections" :key="section.title">
          <dt>{{ section.title }}</dt>
          <dd>{{ section.body }}</dd>
        </template>
      </dl>
    </div>

    <div class="mrt-admin-panel">
      <h2>Arbetsflöde</h2>
      <ol class="mrt-admin-help-steps">
        <li v-for="(step, i) in workflowSteps" :key="i">{{ step }}</li>
      </ol>
    </div>

    <div class="mrt-admin-panel">
      <h2>Drift och avvikelser</h2>
      <ul class="mrt-admin-help-steps">
        <li>
          <strong>Inställ trafik idag</strong> (Översikt) — sätter meddelandet «Inställd» på alla dagens turer.
        </li>
        <li>
          <strong>Avvikelser</strong> (tidtabellseditor) — ändra tågtyp eller meddelande för en specifik tur och datum.
        </li>
        <li>
          <strong>Snabb avgångstid</strong> (mobil) — uppdatera avgång vid första hållplats utan att öppna hela rutnätet.
        </li>
      </ul>
      <p class="description">
        Avvikelser och dagens drift syns i tidtabellsöversikten och i reseplaneraren för berörda datum.
      </p>
    </div>

    <div class="mrt-admin-panel">
      <h2>Shortcodes</h2>
      <p>
        Shortcodes läggs in i innehållet på en WordPress-sida (block «Anpassad HTML» eller
        klassisk redigerare). Varje shortcode visar en del av tidtabellen på webbplatsen.
      </p>
      <p v-if="cfg.isDevMode" class="description">
        I utvecklingsläge: använd <strong>Utvecklingsverktyg → Skapa/uppdatera tidtabellssidor</strong> för
        att skapa en indexsida och en sida per tidtabell med rätt shortcodes.
      </p>

      <article
        v-for="sc in shortcodes"
        :key="sc.tag"
        class="mrt-admin-shortcode"
      >
        <h3>{{ sc.title }}</h3>
        <p><code>[{{ sc.tag }}]</code></p>
        <p>{{ sc.summary }}</p>
        <p class="mrt-admin-shortcode__example">
          <strong>Exempel:</strong>
          <code>{{ sc.example }}</code>
        </p>
        <table v-if="sc.params.length" class="widefat striped mrt-admin-shortcode__params">
          <thead>
            <tr>
              <th>Parameter</th>
              <th>Beskrivning</th>
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
        Mer detaljer om shortcodes, CSV och utvecklingsverktyg finns i
        <code>docs/SHORTCODES.md</code> och övriga filer under <code>docs/</code>.
      </p>
    </div>
  </div>
</template>
