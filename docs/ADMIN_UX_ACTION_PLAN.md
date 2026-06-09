# Åtgärdsplan — admin UX

**Datum:** 2026-06-08  
**Källa:** [feedback/2026-06-08-admin-ux-audit.md](feedback/2026-06-08-admin-ux-audit.md)  
**Syfte:** Prioriterad, genomförbar plan för admin-gränssnittet (Vue SPA). Varje fas ska kunna levereras som en eller flera små PR:er.

**Relaterat:** [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md), [PRICE_ZONES.md](PRICE_ZONES.md), [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md), [frontend/vue/TESTING.md](../frontend/vue/TESTING.md)

---

## Sammanfattning

| Område | Rotorsak | Fas |
|--------|----------|-----|
| Terminologi (Destination) | Etikett matchar inte domän (slutstation längs rutt) | 1 |
| Formulär inkonsistens | Turformulär ombyggt; avvikelser/mobil har gamla mönster | 1–2 |
| Stopptider | Tabell + dold grid; P/A oklart | 2 |
| Priser | Hög kognitiv belastning | 3 |
| Mobil tidtabell | Annat flöde än desktop; begränsad funktion | 2–3 |
| Batch-spar | Avvikelser/trafikdagar sparas inte direkt | 2 |
| Trafikmeddelanden vs avvikelser | Begrepp överlappar i UI | 2 |
| Onboarding/docs | Hints saknas på flera ställen | 1–3 |

**Princip:** Quick wins (l10n, hints, labels) före större refaktor. En vy i taget där möjligt.

---

## Status

| Fas | Beskrivning | Status |
|-----|-------------|--------|
| 0 | UX-audit och åtgärdsplan (detta dokument) | **Klar** |
| 1 | Quick wins — terminologi, hints, små etiketter | **Klar** |
| 2 | Formulärharmonisering — avvikelser, stopptider, mobil | **Klar** |
| 3 | Priser och onboarding — vägledning, länkar | **Klar** |
| 4 | Strukturellt — rutt skapa, batch-spar, ev. gemensam form-komponent | **Klar** |
| 5 | Manuell acceptans + E2E-uppdatering | **Klar** |

---

## Fas 1 — Quick wins (låg risk, 1–2 PR)

Mål: Tydligare språk och hjälptexter utan layout-refaktor.

### 1.1 Slutstation i stället för Destination

| | |
|---|---|
| **Problem** | "Destination" bredvid ruttnamn med ändpunkter förvirrar (audit § terminologi). |
| **Åtgärd** | Uppdatera l10n och ev. E2E-labels. |
| **Filer** | `inc/assets/l10n/editor/admin-vue-l10n-editor-trips.php` (`editorColDestination`, `editorDestinationPrompt`); sök `editorColDestination` i Vue/E2E. |
| **Acceptans** | Alla admin-vyer som visar fältet/kolumnen säger *Slutstation*; befintliga E2E (`admin-timetable-flow.spec.ts`) gröna. |

**Uppgift:** `UX-1.1`

### 1.2 Hint — tidtabellstyp → kalenderfärg

| | |
|---|---|
| **Problem** | Grön/gul/röd/orange saknar förklaring (meta, skapa tidtabell). |
| **Åtgärd** | Ny l10n-nyckel `editorTypeHint`; visa under typ-select. |
| **Filer** | `admin-vue-l10n-editor-trips.php`, `TimetableEditorMetaPanel.vue`, `TimetableListPage.vue` (skapa-vy). |
| **Acceptans** | Operatör ser att typ styr färg i publik kalender. |

**Uppgift:** `UX-1.2`

### 1.3 Inställningar — hints på byte-tider

| | |
|---|---|
| **Problem** | `min_transfer_minutes` / `max_transfer_minutes` utan description (audit §8). |
| **Åtgärd** | Lägg till `settingsMinTransferHint`, `settingsMaxTransferHint` i l10n + `SettingsPage.vue`. |
| **Filer** | `inc/assets/l10n/…/admin-vue-l10n-settings.php` (eller motsvarande), `SettingsPage.vue`. |
| **Acceptans** | Fälten har samma typ av hjälptext som `max_transfers`. |

**Uppgift:** `UX-1.3`

### 1.4 Priser — "Börja här"-länk

| | |
|---|---|
| **Problem** | Prissidan förutsätter kunskap om priszoner. |
| **Åtgärd** | Kort lead-text med `RouterLink` till `#/help?section=price-zones`. |
| **Filer** | `PricesPage.vue`, l10n `pricesHelpLink` / `pricesHelpIntro`. |
| **Acceptans** | Synlig länk ovanför matrisen. |

**Uppgift:** `UX-1.4`

### 1.5 Station — hint om priszoner i formulär

| | |
|---|---|
| **Problem** | Hint finns i listvy men inte vid redigering. |
| **Åtgärd** | Återanvänd/korta `stationsZonesHint` i `StationEditorFields.vue`. |
| **Filer** | `StationEditorFields.vue`, ev. l10n. |
| **Acceptans** | Skapa/redigera station visar hint + länk till hjälp. |

**Uppgift:** `UX-1.5`

### Fas 1 — leverans

```
PR A: UX-1.1 + UX-1.2 (terminologi + tidtabellstyp)
PR B: UX-1.3 + UX-1.4 + UX-1.5 (hints)
```

**Verifiering:** `.\scripts\vue-check.ps1`; manuell koll meta + turformulär + priser.

---

## Fas 2 — Formulär och stopptider (medel risk, 2–3 PR)

Mål: Samma visuella/logiska mönster som turformuläret; tydligare stopptider och mobil.

### 2.1 Avvikelser — harmonisera detaljformulär

| | |
|---|---|
| **Problem** | `mrt-admin-deviation-detail` följer inte `mrt-admin-trip-fields`. |
| **Åtgärd** | Applicera samma CSS-klass/struktur på skapa/redigera i `TimetableEditorDeviationsTab.vue`. |
| **Filer** | `TimetableEditorDeviationsTab.vue`, ev. extrahera gemensam wrapper (valfritt). |
| **Acceptans** | Fältordning och etiketter konsekventa med turformulär; tillbaka + spara oförändrat beteende. |

**Uppgift:** `UX-2.1`

### 2.2 Avvikelser — intro om batch-spar och trafikmeddelanden

| | |
|---|---|
| **Problem** | Batch-spar oklart; skillnad mot generella trafikmeddelanden. |
| **Åtgärd** | `description` högst upp i listvy + `AdminUnsavedBanner` text förtydligas vid behov. |
| **Filer** | `TimetableEditorDeviationsTab.vue`, l10n `editorDeviationsIntro`, `editorDeviationsBatchHint`. |
| **Acceptans** | Ny operatör förstår att spara-knappen gäller hela listan. |

**Uppgift:** `UX-2.2`

### 2.3 Mobil — labels på avvikelse-rad

| | |
|---|---|
| **Problem** | Horisontell rad utan etiketter (`MobileTimetablePanel.vue`). |
| **Åtgärd** | Stackad mini-form eller `getByLabel`-vänliga `<label for=…>`. |
| **Filer** | `MobileTimetablePanel.vue`, `admin-shell.css`. |
| **Acceptans** | Datum och tur har synliga etiketter på mobil; add-knapp oförändrad logik. |

**Uppgift:** `UX-2.3`

### 2.4 Mobil — varning snabb avgång

| | |
|---|---|
| **Problem** | Ändrar bara första stationens avgång utan tydlig varning. |
| **Åtgärd** | `description` under titel i `MobileQuickDeparture.vue`. |
| **Filer** | `MobileQuickDeparture.vue`, l10n `mobileQuickDepartureWarning`. |
| **Acceptans** | Text nämner stationens namn dynamiskt. |

**Uppgift:** `UX-2.4`

### 2.5 Stopptider — P/A-legend och tydligare grid

| | |
|---|---|
| **Problem** | P/A bara som förkortning; grid dold i `<details>`. |
| **Åtgärd** | (a) Legend under tabell med fulltext pickup/dropoff. (b) Grid-sektion öppen som standard **eller** tydligare summary-text + hint ovanför listan. |
| **Filer** | `StopTimesEditor.vue`, `TimetableEditorStoptimesPanel.vue`, l10n. |
| **Acceptans** | Manuell test: redigera tid i tabell och grid — samma värde efter reload. |

**Uppgift:** `UX-2.5`

### 2.6 Trafikdagar — batch-spar hint

| | |
|---|---|
| **Problem** | "Lägg till datum" sparar inte direkt. |
| **Åtgärd** | Kort hint under inline-form i `TimetableEditorDatesTab.vue`. |
| **Filer** | `TimetableEditorDatesTab.vue`, l10n `editorDatesBatchHint`. |
| **Acceptans** | Hint syns när `canManage`; unsaved banner oförändrad. |

**Uppgift:** `UX-2.6`

### 2.7 Trafikmeddelanden — skillnad mot tur-avvikelser

| | |
|---|---|
| **Problem** | Överlappande begrepp (audit §6). |
| **Åtgärd** | Informationsruta (`AdminPanel` / notice) i lista + formulär med länk till tidtabell-avvikelser och hjälp. |
| **Filer** | `TrafficNoticesList.vue`, `TrafficNoticesForm.vue`, l10n traffic-notices. |
| **Acceptans** | Text förklarar att tur-avvikelser redigeras under Tidtabeller → Avvikelser. |

**Uppgift:** `UX-2.7`

### Fas 2 — leverans

```
PR C: UX-2.1 + UX-2.2 (avvikelser desktop)
PR D: UX-2.3 + UX-2.4 (mobil)
PR E: UX-2.5 + UX-2.6 (stopptider + trafikdagar)
PR F: UX-2.7 (trafikmeddelanden)
```

**Verifiering:** `.\scripts\vue-check.ps1`; E2E `admin-timetable-flow.spec.ts`; manuell mobil (devtools).

---

## Fas 3 — Priser och onboarding (medel–hög, 1–2 PR)

Mål: Minska tröskeln för prisadministration utan full wizard.

### 3.1 Priser — gruppera UI visuellt

| | |
|---|---|
| **Problem** | En lång sida med matris + disclosures. |
| **Åtgärd** | Tydligare avsnittsrubriker; ev. numrerad checklista "1. Zoner på stationer → 2. Fyll matris → 3. Förhandsgranska" (statisk text). |
| **Filer** | `PricesPage.vue`, l10n. |
| **Acceptans** | Ny operatör kan följa stegen utan extern doc (smoke-test med kollega). |

**Uppgift:** `UX-3.1`

### 3.2 Hjälp — innehållsförteckning (valfritt)

| | |
|---|---|
| **Problem** | Lång hjälpsida utan hopp-länkar. |
| **Åtgärd** | TOC högst upp i `HelpPage.vue` från `help.adminSections` + fasta paneler. |
| **Filer** | `HelpPage.vue`, `admin-shell.css`. |
| **Acceptans** | Klick scrollar till rätt avsnitt. |

**Uppgift:** `UX-3.2`

### 3.3 Dashboard — behåll onboarding efter complete

| | |
|---|---|
| **Problem** | `SetupChecklist` döljs när allt är klart. |
| **Åtgärd** | `AdminDisclosure` "Kom igång" med checklista även när `complete`. |
| **Filer** | `SetupChecklist.vue` eller `DashboardPage.vue`. |
| **Acceptans** | Checklistan hopfällbar, inte i vägen. |

**Uppgift:** `UX-3.3`

### 3.4 Hjälp — mobil begränsning dokumenterad

| | |
|---|---|
| **Problem** | Mobil tidtabell saknar tur/stopptid-redigering. |
| **Åtgärd** | Stycke i `help.operations` eller FAQ: full redigering kräver bred skärm. |
| **Filer** | PHP help-config (admin l10n help), ev. `MobileTimetablePanel` kort notice. |
| **Acceptans** | FAQ svarar "Kan jag redigera turer i mobilen?" |

**Uppgift:** `UX-3.4`

### Fas 3 — leverans

```
PR G: UX-3.1 + UX-3.4
PR H: UX-3.2 + UX-3.3 (valfritt, kan skjutas)
```

---

## Fas 4 — Strukturellt (högre insats, 1–2 PR)

Mål: Färre fallgropar vid skapa data; säkrare destruktiva flöden.

### 4.1 Rutt skapa — stationer utanför disclosure

| | |
|---|---|
| **Problem** | Tom rutt kan skapas utan stationer (audit §2d). |
| **Åtgärd** | Flytta `RouteStationOrderEditor` ut ur disclosure vid skapa **eller** validera vid spara med tydligt fel. |
| **Filer** | `RoutesPanel.vue`, ev. `useStationsRoutesPage.ts`. |
| **Acceptans** | Kan inte spara rutt utan minst 2 stationer ( eller projektets affärsregel ). |

**Uppgift:** `UX-4.1`

### 4.2 Import — starkare varning vid override

| | |
|---|---|
| **Problem** | Override i disclosure (audit §10). |
| **Åtgärd** | Extra confirm när `mode === 'override'` vid filval; ev. röd notice synlig utanför disclosure. |
| **Filer** | `useImportExportPage.ts`, `ImportExportPage.vue`. |
| **Acceptans** | Override kräver explicit bekräftelse. |

**Uppgift:** `UX-4.2`

### 4.3 Gemensam form-layout (valfritt)

| | |
|---|---|
| **Problem** | Fem olika formmönster (audit § korsvisande teman). |
| **Åtgärd** | Extrahera `AdminFieldStack.vue` (label + slot + description) från `TimetableTripFieldsBlock`. |
| **Filer** | Ny komponent under `admin/components/ui/`; migrera avvikelser, trafikmeddelanden stegvis. |
| **Acceptans** | Ingen visuell regression; Storybook/demo ej krav. |

**Uppgift:** `UX-4.3` (valfritt)

### Fas 4 — leverans

```
PR I: UX-4.1
PR J: UX-4.2
PR K: UX-4.3 (om tid finns)
```

---

## Fas 5 — Acceptans och tester

### 5.1 Manuell acceptanschecklista

Kör [audit § visuell granskningsordning](feedback/2026-06-08-admin-ux-audit.md#rekommenderad-visuell-granskningsordning-45-min) efter fas 1–2. Markera av i audit-dokumentet eller denna fil.

**Uppgift:** `UX-5.1`

### 5.2 E2E-uppdateringar

| Test | Trigger |
|------|---------|
| `admin-timetable-flow.spec.ts` | UX-1.1 (Slutstation-labels) — redan `getByLabel` |
| Nytt: avvikelse batch-spar | UX-2.2 (valfritt) |
| a11y-smoke admin | Efter form-ändringar fas 2 |

**Uppgift:** `UX-5.2`

### 5.3 Rolltest

Verifiera `can_operate` (utan manage) för: tidtabell, avvikelser, stopptider, trafikmeddelanden.

**Uppgift:** `UX-5.3`

---

## Uppgiftstabell (tracker)

| ID | Titel | Fas | Prioritet | Status |
|----|-------|-----|-----------|--------|
| UX-1.1 | Slutstation i l10n | 1 | P1 | ☑ |
| UX-1.2 | Hint tidtabellstyp | 1 | P2 | ☑ |
| UX-1.3 | Hints transfer-inställningar | 1 | P3 | ☑ |
| UX-1.4 | Priser — hjälplänk | 1 | P2 | ☑ |
| UX-1.5 | Station — priszon-hint | 1 | P2 | ☑ |
| UX-2.1 | Avvikelser form-layout | 2 | P1 | ☑ |
| UX-2.2 | Avvikelser intro/batch | 2 | P1 | ☑ |
| UX-2.3 | Mobil avvikelse-labels | 2 | P1 | ☑ |
| UX-2.4 | Mobil snabb avgång-varning | 2 | P2 | ☑ |
| UX-2.5 | Stopptider P/A + grid | 2 | P0 | ☑ |
| UX-2.6 | Trafikdagar batch-hint | 2 | P2 | ☑ |
| UX-2.7 | Trafikmeddelanden vs avvikelser | 2 | P2 | ☑ |
| UX-3.1 | Priser onboarding-text | 3 | P0 | ☑ |
| UX-3.2 | Hjälp TOC | 3 | P3 | ☑ |
| UX-3.3 | Dashboard checklist kvar | 3 | P3 | ☑ |
| UX-3.4 | Mobil begränsning i FAQ | 3 | P2 | ☑ |
| UX-4.1 | Rutt skapa validering | 4 | P1 | ☑ |
| UX-4.2 | Import override confirm | 4 | P3 | ☑ |
| UX-4.3 | AdminFieldStack (valfritt) | 4 | — | ☑ |
| UX-5.1 | Manuell acceptans | 5 | — | ☐ |
| UX-5.2 | E2E | 5 | — | ☑ |
| UX-5.3 | Rolltest operate | 5 | — | ☐ |

**Prioritet från audit:** P0 = stopptider + priser · P1 = avvikelser, rutt, terminologi · P2–P3 = övrigt.

---

## Rekommenderad ordning (minimal viable UX)

Om tiden är begränsad, gör **endast** detta för störst effekt:

1. **UX-1.1** — Slutstation (30 min)
2. **UX-2.5** — Stopptider legend + synligare grid (2–4 h)
3. **UX-2.1 + UX-2.3** — Avvikelser desktop + mobil (3–4 h)
4. **UX-1.4 + UX-3.1** — Priser vägledning (2 h)
5. **UX-2.7** — Trafikmeddelanden förklaring (1 h)

Därefter fas 1 rest + fas 4.1.

---

## Definition of done (hela planen)

- [ ] Alla P0- och P1-uppgifter UX-1.1 … UX-4.1 markerade klara
- [ ] `.\scripts\check.ps1 -Vue` grön
- [ ] Manuell acceptans enligt audit §11 genomförd (desktop + mobil)
- [ ] Rolltest `can_operate` dokumenterat OK
- [ ] Audit-dokument uppdaterat med "Implementerat"-notiser eller länk hit

---

## Underhåll

- Vid **nya admin-vyer**: följ `mrt-admin-trip-fields`-mönster (label ovanför, max-width, description).
- Vid **l10n-ändringar**: kör E2E som använder `getByLabel` / fixtures `admin-strings.mjs`.
- Uppdatera denna plan när faser slutförs (statuskolumn + tracker).

---

*Skapad utifrån [2026-06-08-admin-ux-audit.md](feedback/2026-06-08-admin-ux-audit.md).*
