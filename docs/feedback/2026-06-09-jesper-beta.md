# Feedback – Jesper (beta, juni 2026)

Ny omgång feedback efter fortsatt betatest av reseplaneraren och admingränssnittet. **Gå igenom en punkt i taget** — bocka av status när punkt är besvarad, fixad eller avvisad.

**Källor:** mail/skärmdumpar från Jesper (juni 2026)  
**Senast uppdaterad:** 2026-06-11 — status synkad mot kod, [TODO.md](../TODO.md) och [ADMIN_UX_ACTION_PLAN.md](../ADMIN_UX_ACTION_PLAN.md)  
**Relaterat:** [2026-06-05-reseplanerare-beta.md](2026-06-05-reseplanerare-beta.md), [2026-06-08-admin-ux-audit.md](2026-06-08-admin-ux-audit.md), [2026-06-09-jesper-buggar-plan.md](2026-06-09-jesper-buggar-plan.md), [2026-06-09-jesper-diskussioner.md](2026-06-09-jesper-diskussioner.md), [ADMIN_UX_ACTION_PLAN.md](../ADMIN_UX_ACTION_PLAN.md)

---

## Sammanfattning

| Kategori | Antal | Klar | Kvar |
|----------|-------|------|------|
| Reseplanerare — buggar | 1 | 1 (J5) | — |
| Reseplanerare — UI / copy | 8 | 8 (J1–J4, J7–J10) | — |
| Reseplanerare — data | 1 | 1 (J6 fixture) | operatörsdata (Jesper) |
| Admin — onboarding / friktion | 2 | 1 (A10) | A0 |
| Admin — produktförslag | 9 | 8 (A1–A8, A10) | A9 |
| Admin — Turvy / tidtabellsöversikt | 3 | 3 (A2, J12, uppföljning) | manuell validering |
| Framtida scope | 2 | 1 (J13 beta) | J11 |
| Produktbeslut | 1 | 1 (J13) | — |

---

## Reseplanerare

### J1. Grått fält — bara ikoner på en rad

- **Originaltext:** I det gråa fältet uppe föreslår jag att istället för att skriva ut alla tåg bara ha symbolerna på en rad för att spara plats.
- **Område:** Reseplanerare / resekort
- **Typ:** UX
- **Prioritet:** låg
- **Status:** beslutad (D7 C) — implementerad
- **Tekniskt:** `MrtVehicleRow` med `compact` i `WizardTripCard.vue`; full text kvar i `WizardDetailSegment.vue`.

---

### J2. Pil vid ”N byten” — vertikal centrering

- **Originaltext:** Pilen till höger om ”2 byten” skulle vara snyggare centrerad i höjdled.
- **Område:** Reseplanerare / resekort
- **Typ:** CSS
- **Prioritet:** låg
- **Status:** klar
- **Tekniskt:** `.mrt-expand-trigger__chevron` i `assets/frontend/ui/trips.css`; label i egen flex-rad + optisk `translateY` på pilen.

---

### J3. Tidslinje — linje inte centrerad mellan stationer

- **Originaltext:** Linjen mellan stationerna är nu inte riktigt centrerad, hade varit snyggt om man kunde fixa det.
- **Område:** Reseplanerare / detaljvy
- **Typ:** CSS
- **Prioritet:** låg
- **Status:** klar (2026-06-09)
- **Tekniskt:** Nodbredd (`1rem`) matchade inte kolumnvariabeln `--mrt-tl-node` — `justify-self: center` på `.mrt-timeline__node`.

---

### J4. ”Ca” vid hållplatser utan exakt tid i tidtabell

- **Originaltext:** Vid hållplatser är ju tiden ungefärlig. Hade varit bra om ”Ca 10.13” etc vid de tider som inte är angivna i tjänstetidtabellerna.
- **Område:** Reseplanerare / detaljvy + data
- **Typ:** feature
- **Prioritet:** medium
- **Status:** klar (fas 3 v1, detaljvy)
- **Tekniskt:** Journey-detalj skickar `time_label` + behov-flaggor; `WizardTimeline` visar Ca/X; fotnoter i `WizardDetailSegment`.
- **Koppling:** Admin A3 (behovsuppehåll → Ca).

---

### J5. Fel bytestid vid Selknä (10 min i stället för 3 min)

- **Originaltext:** Byter i Selknä är tre minuter i bilden ovan, men är angivet som 10 minuter.
- **Område:** Reseplanerare / detaljvy
- **Typ:** bugg
- **Prioritet:** hög
- **Status:** klar (2026-06-09)
- **Fix:** `transferWaitMinutes()` beräknar alltid från leg-par; ignorerar connection-nivå `transfer_wait_minutes`.

---

### J6. Linnés Hammarby — inga resor / inga dagar

- **Originaltext:** Linnés Hammarby-turer syns inte alls, men det verkar vara ett inläsningsfel då rutten inte finns med i adminpanelen.
- **Område:** Data / admin
- **Typ:** data (operatör)
- **Prioritet:** medium
- **Status:** beslutad (D17 C) — fixture + importguide
- **Anteckning:** CSV-gren i `testdata/fixtures/lennakatten/`; import via `#/import-export`. Se [LINNES_HAMMARBY.md](../LINNES_HAMMARBY.md).

---

### J7. Copy — ”Inga bokningsbara dagar…”

- **Originaltext:** Det låter konstigt med ”Inga bokningsbara dagar denna månad för din resa”, eftersom man inte bokar dagar eller tåg heller för den delen.
- **Område:** Reseplanerare / datumsteg
- **Typ:** copy
- **Prioritet:** medium
- **Status:** beslutad (D1 B) — implementerad
- **Tekniskt:** `calendarEmptyMonth`, `legendOk` i `vue-shortcode-config.php` / `WizardDateStep.vue`.

---

### J8. Steg-rutorna klickbara när klara

- **Originaltext:** De fyra rutorna överst (”välj utresa” etc) ska vara klickbara när de blivit gula, så man snabbt kan gå tillbaka till ett tidigare steg.
- **Område:** Reseplanerare / navigation
- **Typ:** UX
- **Prioritet:** medium
- **Status:** klar (2026-06-10)
- **Tekniskt:** `MrtStepProgress` med `:readonly="false"`; klick på klara steg navigerar tillbaka i `JourneyWizardApp.vue` / wizard store.

---

### J9. Font-weight — Bold 700, inte Extra Bold 800

- **Originaltext:** Fonten är ändrad till Open Sans, men det ser ut som Extra Bold 800 — ska vara Bold 700.
- **Område:** Reseplanerare / typografi
- **Typ:** design
- **Prioritet:** medium
- **Status:** beslutad (D5 A) — implementerad
- **Tekniskt:** `font-weight: 700` i hela publika Vue-UI. Se [D5](2026-06-09-jesper-diskussioner.md#d5-font-weight-open-sans-700-).

---

### J10. Desktop — full bredd och bakgrundsbild

- **Originaltext:** Reseplaneraren borde vara centrerad och täcka från vänster till höger kant. Mycket grönt — bakgrund som skalbar bild (likt UL:s hemsida).
- **Område:** Reseplanerare / layout
- **Typ:** design
- **Prioritet:** låg–medium
- **Status:** klar (2026-06-10)
- **Tekniskt:** Desktop hero full bredd (`hero-layout.css`); valfri bakgrundsbild via `--mrt-wizard-hero-bg-image` (shortcode/settings + admin mediaväljare).

---

### J11. Trafikstörningar — UL-lik lista

- **Originaltext:** UI för trafikstörningar bör utvecklas — lista på nuvarande och kommande störningar, ange vilka tåg som berörs (likt UL).
- **Område:** Publik webb (shortcode / ev. egen sida) — **inte** reseplaneraren
- **Typ:** framtida scope
- **Prioritet:** låg
- **Status:** **beslutad riktning** (2026-06-11) — implementation enligt [TRAFFIC_DISRUPTIONS_PLAN.md](../TRAFFIC_DISRUPTIONS_PLAN.md); väntar Jesper-validering målbild + fas 1
- **Beslut:** Webb only; **två källor → en feed** — (A) auto från tur-avvikelser + (B) manuella trafikmeddelanden; 90 dagars horisont; gruppering tågnummer; ingen wizard/realtime
- **Tekniskt:** v1 shortcode + `aggregate.php` finns; v2 = disruption feed API + UL-lik Vue-feed

---

### J12. Admin — Samtrafiken-lik överblick (referens)

- **Originaltext:** UI inspirerat av Samtrafikens Trafikdataportal — turer bredvid varandra för enkel överblick.
- **Område:** Admin
- **Typ:** produktförslag
- **Prioritet:** medium
- **Status:** klar (2026-06-10) — samma leverans som A2
- **Tekniskt:** Flik **Turvy** med `EditableTimetableOverview`; uppföljning 2026-06-10: kolumnsammanslagning vid tågbyte (`9a44dda`), bussrader per tågkolumn vid Selknä (`8c8a30a`), typografi utan fetstil (`6d91237`). Manuell PDF-validering kvar — se [TODO.md](../TODO.md).
- **Se:** Admin A2 nedan.

---

### J13. Beta-lansering under säsongen

- **Originaltext:** Provlansera reseplaneraren som beta under säsongen för att samla feedback och hitta buggar innan bred lansering.
- **Område:** Produkt / lansering
- **Typ:** produktbeslut
- **Prioritet:** info
- **Status:** beslutad (D2 A) — implementerad
- **Svar:** Beta-banner styrs under **Inställningar → Visa beta-banner** (`wizard_beta_enabled`). Feedback-widget (FAB) är separat toggle — beslut 2026-06-10: oberoende toggles, valfri e-post, GDPR-text i panel, e-postnotis v2. Se [WIZARD_FEEDBACK_SKETCH.md](../WIZARD_FEEDBACK_SKETCH.md) och [TODO.md](../TODO.md).

---

## Admin — onboarding och inmatning

### A0. Upplevd friktion vid inmatning (Linnés Hammarby)

- **Originaltext:** Omständligt att lägga till rutt per riktning, stationer, start/slutstation, tidtabell, tider, av/på, slutstation, stannar/stannar inte. Tidtabellsskaparen förstår inte vilken buss som ansluter till vilket tåg — anslutningar Uppsala↔Linnés Hammarby resp. Fjällnora känns godtyckliga.
- **Område:** Admin / hela onboarding-flödet
- **Typ:** UX / produkt
- **Prioritet:** hög
- **Status:** öppen
- **Nuvarande flöde:** Rutter (`RoutesPanel`) → tidtabell → turer (`TimetableTripFieldsBlock`) → stopptider (tabell eller grid). Busskoppling: `train_change_map` per station + automatisk matchning i `grid-connections.php`.
- **Relaterat:** [ADMIN_WORKFLOW.md](../ADMIN_WORKFLOW.md), [2026-06-08-admin-ux-audit.md](2026-06-08-admin-ux-audit.md)

---

### A1. Rutt — ta bort manuell start/slutstation

- **Originaltext:** När man skapar en rutt lägger man till stationerna i ordning som nu. Ta bort att man behöver ange start- och slutstation — systemet bör förstå att slutstationen är sista stationen man anger.
- **Område:** Admin / rutter
- **Typ:** UX
- **Prioritet:** medium
- **Status:** beslutad (D9 A) — implementerad
- **Tekniskt:** Start/slut sätts automatiskt från första/sista i `station_ids` (admin + REST). Se [D9](2026-06-09-jesper-diskussioner.md#d9-auto-startslutstation-a1-).

---

### A2. Turvy som Trafikdataportalen

- **Originaltext:** Turer visas bredvid varandra; möjlighet att lägga in ankomst- och avgångstider direkt i gridet.
- **Område:** Admin / tidtabellsredigerare
- **Typ:** produktförslag (stor)
- **Prioritet:** hög
- **Status:** klar (fas 2, D10 A delvis) — uppföljning 2026-06-10
- **Tekniskt:** Egen flik **Turvy** med `EditableTimetableOverview`; turer skapas fortfarande under Turer, tider fylls primärt i grid. Uppföljning: PDF-lik kolumnsammanslagning vid tågbyte (Marielund m.fl.), bussrader kopplade till rätt tågkolumn vid Selknä, Ca/P/A/X-typografi i Turvy.

---

### A3. X före tid = behovsuppehåll → Ca + fotnot i reseplanerare

- **Originaltext:** Skriver man X före tiden blir det behovsuppehåll, markerat med ”ca” i reseplaneraren. Fotnot i utökad vy: *”Behovsuppehåll, ge ett tecken till föraren om du vill stiga på”* / *”…säg till konduktören i god tid om du vill stiga av”*.
- **Område:** Admin inmatning + reseplanerare visning
- **Typ:** feature
- **Prioritet:** medium
- **Status:** klar (fas 3 v1, D14 A)
- **Tekniskt:** På/Av-kryssrutor; båda + tid → Ca i wizard (`MRT_journey_stop_wizard_time_meta`); fotnoter i detaljvy.
- **Koppling:** J4.

---

### A4. P = behovsuppehåll påstigande, A = avstigande

- **Originaltext:** P före tid = behovsuppehåll endast påstigande; A endast avstigande.
- **Område:** Admin / stopptider
- **Typ:** feature (delvis finns)
- **Prioritet:** medium
- **Status:** klar (fas 3 v1)
- **Tekniskt:** Tydligare P/A-etiketter + legend i Turvy/stopptider (`stopTimesOnRequestHint`).

---

### A5. Tom rad = tåget stannar inte

- **Originaltext:** Lämnas en rad tom (utan angiven tid) förstår systemet att tåget inte stannar där.
- **Område:** Admin / stopptider
- **Typ:** UX
- **Prioritet:** medium
- **Status:** klar
- **Tekniskt:** `finalizeGridCellEdit` i grid-dialog; hint i cell-editor.

---

### A6. Bara ankomst eller avgång → samma tid

- **Originaltext:** Om man endast anger ankomst- eller avgångstid för en station förstår systemet att ankomst- och avgångstid är samma.
- **Område:** Admin / stopptider
- **Typ:** UX
- **Prioritet:** låg
- **Status:** klar
- **Tekniskt:** `MRT_mirror_stoptime_arrival_departure` i `stoptimes-persist.php` vid bulk-sparande (lista + grid).

---

### A7. Sista station med tid = turens slutstation

- **Originaltext:** Den sista stationen som har en tid på en tur förstår systemet är slutstationen för den turen.
- **Område:** Admin / turer
- **Typ:** UX
- **Prioritet:** medium
- **Status:** klar (2026-06-09, D13 A)
- **Tekniskt:** `MRT_sync_service_end_station_from_stops()` vid stopptidssparande; manuellt destinationsfält borttaget i turformulär. Se [D13](2026-06-09-jesper-diskussioner.md#d13-auto-slutstation-från-stopptider-a7-).

---

### A8. Zoner A, B, C i stället för 1, 2, 3

- **Originaltext:** Zonerna kallas A, B och C istället för 1, 2 och 3.
- **Område:** Admin (+ ev. publik)
- **Typ:** UX / copy
- **Prioritet:** låg
- **Status:** beslutad (D12 C) — implementerad
- **Tekniskt:** Lagring numerisk; visning A–D via `formatPriceZoneLabel` i admin och reseplanerare.

---

### A9. Publicera-knapp (utkast innan publik synlighet)

- **Originaltext:** En publicera-knapp så man kan förbereda alla tider innan det syns för allmänheten.
- **Område:** Admin / tidtabell
- **Typ:** feature (stor)
- **Prioritet:** medium
- **Status:** öppen
- **Tekniskt:** Tidtabeller skapas som `publish` direkt (`MRT_rest_create_timetable`). Kräver `draft`-status, filter i REST/kalender/wizard, explicit publish-action.

---

### A10. Bussanslutningar — riktning och gren

- **Originaltext:** (Utifrån A0) Systemet ska visa anslutningar Uppsala↔Linnés Hammarby resp. Fjällnora konsekvent, inte godtyckligt.
- **Område:** Admin / tidtabell + stationer
- **Typ:** produkt / utred
- **Prioritet:** hög
- **Status:** klar (2026-06-09) — uppföljning 2026-06-10
- **Fix:** `paired_branches` i `grid-merge.php`; tidtabellsöversikt bygger bussrader per gren (Linnés Hammarby + Fjällnora). Uppföljning (`8c8a30a`): busstider i **rätt tågkolumn** vid knutpunkt (Selknä), inte i alla kolumner samtidigt — enhetstest `test_junction_bus_rows_use_one_pair_per_matched_train`. Manuell check Selknä kvar i [TODO.md](../TODO.md).

---

## Prioriterad genomgångslista

| # | ID | Punkt | Insats | Status |
|---|-----|-------|--------|--------|
| 1 | J5 | Fel bytestid Selknä (flera byte) | Liten fix + test | ☑ |
| 2 | J7 | Copy ”bokningsbar” → ”trafik för din resa” | Liten | ☑ |
| 3 | J9 | Font-weight 700 | Liten (CSS) | ☑ |
| 4 | A1 | Auto start/slut vid rutt | Medel | ☑ |
| 5 | A7 | Auto slutstation från stopptider | Medel | ☑ |
| 6 | A8 | Zon-etiketter A/B/C | Liten (UI) | ☑ |
| 7 | J1–J3 | Ikoner, pil, tidslinje | Liten (CSS) | ☑ |
| 8 | J8 | Klickbara steg | Medel | ☑ |
| 9 | A2 | Samtrafiken-grid som primär vy | Stor | ☑ |
| 10 | A3–A6 | X/P/A, tom rad, en tid räcker | Medel–stor | ☑ |
| 11 | A9 | Utkast/publicera tidtabell | Stor | öppen |
| 12 | A10 | Buss/tåg per riktning och gren | Stor | ☑ (+ Selknä 2026-06-10) |
| 13 | J4 + A3 | Ca + fotnot behovsuppehåll | Medel–stor | ☑ |
| 14 | J10 | Desktop fullbredd + bakgrundsbild | Design + asset | ☑ |
| 15 | J11 | Trafikstörningar UL-lik | Framtida | beslutad riktning — [plan](../TRAFFIC_DISRUPTIONS_PLAN.md) |
| 16 | J12 | Samtrafiken-lik överblick (Turvy) | Stor | ☑ (+ PDF-lik vy 2026-06-10) |

---

## Föreslagen admin-roadmap

**D18 (2026-06-09):** Fas 1 först — **A7** ✓ (+ ev. A6); J2 polish. J8 skjuts.

1. **Fas 1** — A1 ✓, A7 ✓, A6 ✓, A8 ✓ (färre manuella fält)
2. **Fas 2** — A2 ✓, A5 ✓ (grid som primär stopptidsinmatning)
3. **Fas 3** — A3 ✓, A4 ✓, J4 ✓ (behovsuppehåll v1 — detaljvy)
4. **Fas 4** — A10 ✓; **A9** (publicera/utkast) kvar

---

## Nästa steg

- [x] Bocka av **Status** per punkt efter genomgång (2026-06-10)
- [x] Turvy-uppföljning: kolumnsammanslagning, Selknä-bussrader, typografi (2026-06-10)
- [ ] Länka commit/PR i **Svar** när punkt är åtgärdad
- [ ] Parallellt: Jesper fortsätter mata in Linnés Hammarby-data — verifiera reseplanerare efter komplett rutt/turer/stopptider
- [ ] **Öppna punkter:** A9 (publicera), A0 (onboarding-friktion)
- [ ] **J11:** Jesper OK på målbild §5.2 + 90 dagar; sedan fas 1 — [TRAFFIC_DISRUPTIONS_PLAN.md](../TRAFFIC_DISRUPTIONS_PLAN.md)
- [ ] Manuell smoke: Selknä-buss + Marielund-kolumner enligt [TODO.md](../TODO.md) och [buggplan](2026-06-09-jesper-buggar-plan.md#verifiering-gemensam-checklista)

---

## Bilder (denna omgång)

| Beskrivning | Kopplad punkt |
|-------------|---------------|
| Resekort grått fält, 2 byten, tidslinje | J1, J2, J3, J4 |
| Byte Selknä 10 min vs 3 min | J5 |
| Kalender Linnés Hammarby, inga gula dagar | J6, J7 |
| Steg-navigering, font, desktop sök | J8, J9, J10 |
| Admin tidtabellsgrid (referens Samtrafiken) | J12, A2 |

> Skärmdumpar från Jesper (juni 2026) — ev. arkivera i `docs/feedback/images/` med datum i filnamn.
