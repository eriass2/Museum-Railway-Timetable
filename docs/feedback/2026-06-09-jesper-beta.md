# Feedback – Jesper (beta, juni 2026)

Ny omgång feedback efter fortsatt betatest av reseplaneraren och admingränssnittet. **Gå igenom en punkt i taget** — bocka av status när punkt är besvarad, fixad eller avvisad.

**Källor:** mail/skärmdumpar från Jesper (juni 2026)  
**Relaterat:** [2026-06-05-reseplanerare-beta.md](2026-06-05-reseplanerare-beta.md), [2026-06-08-admin-ux-audit.md](2026-06-08-admin-ux-audit.md), [2026-06-09-jesper-buggar-plan.md](2026-06-09-jesper-buggar-plan.md), [2026-06-09-jesper-diskussioner.md](2026-06-09-jesper-diskussioner.md), [ADMIN_UX_ACTION_PLAN.md](../ADMIN_UX_ACTION_PLAN.md)

---

## Sammanfattning

| Kategori | Antal | Kommentar |
|----------|-------|-----------|
| Reseplanerare — buggar | 1 | Fel bytestid vid flera byte (Selknä) |
| Reseplanerare — UI / copy | 8 | Ikoner, centrering, Ca-tider, steg-nav, copy, font, layout |
| Reseplanerare — data | 1 | Linnés Hammarby (rutt saknas i admin) |
| Admin — onboarding / friktion | 2 | Många steg; bussanslutningar otydliga |
| Admin — produktförslag | 9 | Auto-fält, Samtrafiken-grid, X/P/A, zoner A/B/C, publicera |
| Framtida scope | 3 | Bakgrundsbild, trafikstörningar (UL-lik), beta-lansering |
| Produktbeslut | 1 | Provlansera reseplaneraren som beta under säsongen |

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
- **Status:** öppen
- **Tekniskt:** `.mrt-expand-trigger__chevron` i `assets/frontend/ui/trips.css`.

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
- **Status:** öppen
- **Tekniskt:** Tidtabellsöversikten har P/A/X-prefix (`MRT_stop_time_prefix_and_time_parts`); journey-detalj-API skickar rå stopptider utan approx-flagga. Kräver backend + `WizardTimeline`.
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
- **Status:** öppen
- **Tekniskt:** `calendarEmptyMonth`, `legendOk` i `WizardDateStep.vue` / `inc/assets/frontend.php`. Förslag: *”Inga resbara dagar…”* / *”Ingen trafik för din resa”*.

---

### J8. Steg-rutorna klickbara när klara

- **Originaltext:** De fyra rutorna överst (”välj utresa” etc) ska vara klickbara när de blivit gula, så man snabbt kan gå tillbaka till ett tidigare steg.
- **Område:** Reseplanerare / navigation
- **Typ:** UX
- **Prioritet:** medium
- **Status:** öppen
- **Tekniskt:** `MrtStepProgress` är `readonly: true`; behöver navigation till tidigare steg i `JourneyWizardApp.vue` / wizard store.

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
- **Status:** öppen
- **Tekniskt:** Hero max ~58rem; solid `--mrt-wizard-green-dark`. Kräver layoutbeslut + bildasset.

---

### J11. Trafikstörningar — UL-lik lista

- **Originaltext:** UI för trafikstörningar bör utvecklas — lista på nuvarande och kommande störningar, ange vilka tåg som berörs (likt UL).
- **Område:** Admin + publik visning
- **Typ:** framtida scope
- **Prioritet:** låg
- **Status:** parkerad
- **Tekniskt:** `TimetableEditorDeviationsTab`, trafikmeddelanden — grund finns, behöver utvecklas.

---

### J12. Admin — Samtrafiken-lik överblick (referens)

- **Originaltext:** UI inspirerat av Samtrafikens Trafikdataportal — turer bredvid varandra för enkel överblick.
- **Område:** Admin
- **Typ:** produktförslag
- **Prioritet:** medium
- **Status:** öppen
- **Se:** Admin A2 nedan.

---

### J13. Beta-lansering under säsongen

- **Originaltext:** Provlansera reseplaneraren som beta under säsongen för att samla feedback och hitta buggar innan bred lansering.
- **Område:** Produkt / lansering
- **Typ:** produktbeslut
- **Prioritet:** info
- **Status:** beslutad (D2 A) — implementerad
- **Svar:** Beta-banner i reseplaneraren via `[museum_journey_wizard beta="1" beta_feedback_url="…"]`. Filter: `mrt_journey_wizard_beta_banner_enabled`, `mrt_journey_wizard_beta_feedback_url`.

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
- **Status:** öppen
- **Tekniskt:** Delvis finns (`OverviewGridCellEditor`, tidtabellsgrid). Turer skapas fortfarande i separat formulär med slutstation m.m. — gör gridet till primär inmatningsvy.

---

### A3. X före tid = behovsuppehåll → Ca + fotnot i reseplanerare

- **Originaltext:** Skriver man X före tiden blir det behovsuppehåll, markerat med ”ca” i reseplaneraren. Fotnot i utökad vy: *”Behovsuppehåll, ge ett tecken till föraren om du vill stiga på”* / *”…säg till konduktören i god tid om du vill stiga av”*.
- **Område:** Admin inmatning + reseplanerare visning
- **Typ:** feature
- **Prioritet:** medium
- **Status:** öppen
- **Tekniskt:** P/A/X finns i PHP för tidtabellsvisning; admin-grid använder dialog + kryssrutor. Kräver enhetlig inmatning, API-flagga, wizard-fotnot.
- **Koppling:** J4.

---

### A4. P = behovsuppehåll påstigande, A = avstigande

- **Originaltext:** P före tid = behovsuppehåll endast påstigande; A endast avstigande.
- **Område:** Admin / stopptider
- **Typ:** feature (delvis finns)
- **Prioritet:** medium
- **Status:** öppen
- **Tekniskt:** `pickup_allowed` / `dropoff_allowed` + `StopTimePaCheckbox`; textprefix i cell eller tydligare UI.

---

### A5. Tom rad = tåget stannar inte

- **Originaltext:** Lämnas en rad tom (utan angiven tid) förstår systemet att tåget inte stannar där.
- **Område:** Admin / stopptider
- **Typ:** UX
- **Prioritet:** medium
- **Status:** öppen
- **Tekniskt:** Stöds delvis via `stopsHere` i grid; gör beteendet tydligt vid inline-inmatning.

---

### A6. Bara ankomst eller avgång → samma tid

- **Originaltext:** Om man endast anger ankomst- eller avgångstid för en station förstår systemet att ankomst- och avgångstid är samma.
- **Område:** Admin / stopptider
- **Typ:** UX
- **Prioritet:** låg
- **Status:** öppen
- **Tekniskt:** Normalisera vid spara i stopptids-API / grid-redigerare.

---

### A7. Sista station med tid = turens slutstation

- **Originaltext:** Den sista stationen som har en tid på en tur förstår systemet är slutstationen för den turen.
- **Område:** Admin / turer
- **Typ:** UX
- **Prioritet:** medium
- **Status:** öppen
- **Tekniskt:** Idag manuellt `end_station_id` i `TimetableTripFieldsBlock` / `mrt_service_end_station_id`. Härleda från stopptider vid spara.

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
- **Status:** klar (2026-06-09)
- **Fix:** `paired_branches` i `grid-merge.php`; tidtabellsöversikt bygger bussrader per gren (Linnés Hammarby + Fjällnora).

---

## Prioriterad genomgångslista

| # | ID | Punkt | Insats |
|---|-----|-------|--------|
| 1 | J5 | Fel bytestid Selknä (flera byte) | Liten fix + test |
| 2 | J7 | Copy ”bokningsbara” → ”resbara” | Liten |
| 3 | J9 | Font-weight 700 | Liten (CSS) |
| 4 | A1 | Auto start/slut vid rutt | Medel |
| 5 | A7 | Auto slutstation från stopptider | Medel |
| 6 | A8 | Zon-etiketter A/B/C | Liten (UI) |
| 7 | J1–J3 | Ikoner, pil, tidslinje | Liten (CSS) |
| 8 | J8 | Klickbara steg | Medel |
| 9 | A2 | Samtrafiken-grid som primär vy | Stor |
| 10 | A3–A6 | X/P/A, tom rad, en tid räcker | Medel–stor |
| 11 | A9 | Utkast/publicera tidtabell | Stor |
| 12 | A10 | Buss/tåg per riktning och gren | Stor |
| 13 | J4 + A3 | Ca + fotnot behovsuppehåll | Medel–stor |
| 14 | J10 | Desktop fullbredd + bakgrundsbild | Design + asset |
| 15 | J11 | Trafikstörningar UL-lik | Framtida |

---

## Föreslagen admin-roadmap

1. **Fas 1** — A1, A7, A6, A8 (färre manuella fält)
2. **Fas 2** — A2, A5 (grid som primär stopptidsinmatning)
3. **Fas 3** — A3, A4, J4 (behovsuppehåll + reseplanerare)
4. **Fas 4** — A9, A10 (publicera + anslutningar)

---

## Nästa steg

- [ ] Bocka av **Status** per punkt efter genomgång
- [ ] Länka commit/PR i **Svar** när punkt är åtgärdad
- [ ] Parallellt: Jesper fortsätter mata in Linnés Hammarby-data — verifiera reseplanerare efter komplett rutt/turer/stopptider

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
