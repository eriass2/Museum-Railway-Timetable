# Journey wizard – verklighetsanpassning

**Status:** Visuell bas (steg 1–2, färgpalett) är implementerad. Mockups i `docs/mockups/` är **arkiverad referens** — nya beslut ska utgå från riktig data, API och användartest, inte PNG.

**Style guide:** [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md) + `assets/mrt-color-tokens.css`.

---

## Prioriterad ordning

### 1. Data & API (sanningen)
- [ ] Rutt: stationer från WordPress (redan i shortcode-config), validering mot faktiska rutter
- [ ] Kalender: `mrt_journey_calendar_month` med riktig trafik för vald rutt/restyp
- [ ] Utresa/retur: `mrt_search_journey` — direkt, byte, varningar (`notice`)
- [ ] Detalj: `mrt_journey_connection_detail` för expanderade kort
- [ ] Priser i sammanfattning: `priceTickets` / zoner från admin-config, matchar vald rutt

### 2. Innehåll & WordPress
- [ ] Svenska strängar i `vue-shortcode-config.php` — granska med beställare
- [ ] Shortcode på riktig sida: `ticket_url`, `timetable_page_url`, ingen debug
- [ ] Bort med demo-texter (“mockup”, fixture) på publika sidor
- [ ] `embedded="1"` endast i admin/demo, fullbredds-hero på produktionssida

### 3. UX utifrån verkliga fall
- [ ] Tom kalender / inga anslutningar — tydliga felmeddelanden
- [ ] Enkel resa vs tur och retur — steglista och API-parametrar
- [ ] Stationssök: fungerar med alla stationer i databasen (diakritik, dubbletter)
- [ ] Tillgänglighet: tangentbord genom hela flödet (fokus, aria)
- [ ] Mobil: kalender, turkort, prismatris

### 4. Steg 3–5 visuellt (utan mockup-jakt)
- [ ] Utresa/retur: turkort mot vit/grön panel (tokens, inte pixelperfekt PNG)
- [ ] Sammanfattning: läsbar prismatris, biljettknapp
- [ ] Enhetlig guld/grön med steg 1–2

### 5. Kvalitet
- [ ] Manuell checklista: [frontend/vue/TESTING.md](../frontend/vue/TESTING.md) mot demosida med **imported** Lennakatten-data
- [ ] E2E mot Docker: `MRT_E2E_WP_URL=… npm run e2e`
- [ ] `composer vue:check` i CI

---

## Inte längre mål
- Pixelperfekt match mot `docs/mockups/*.png`
- Nya CSS-hex utanför `mrt-color-tokens.css`
- Inbäddad tidtabell HTML i steg 1 (ersatt av länk `timetable_page_url`)

---

## Debug (endast utveckling)
Fixture-sidor och `debug="date|outbound|…"` — se [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md). Använd för layout, inte som acceptanskriterium för produktion.
