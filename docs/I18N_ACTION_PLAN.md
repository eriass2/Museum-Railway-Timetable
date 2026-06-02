# Åtgärdsplan – översättningar (i18n)

Text domain: `museum-railway-timetable`. Publikt Vue får strängar från PHP (`strings`, `wizard`, `labels`). Admin Vue saknar i18n (fas 5).

## Status

| Fas | Beskrivning | Status |
|-----|-------------|--------|
| 1 | Dokumentation och wizard PHP/Vue | Klar |
| 2 | Månadskalender – legend-hints från config | Klar |
| 3 | Tidtabellsöversikt – UI-etiketter via `strings` | Klar |
| 4 | Delade komponenter (`MrtAsyncState`, tåg-fallback) | Klar |
| 5 | Admin Vue – `mrtAdminVue.strings` | Påbörjad (nav, gemensamt, inställningar, priser) |

## Fas 1 – Wizard

- [x] Lägg till `calPrevAria`, `calNextAria` i `MRT_vue_wizard_config()` → `labels`
- [x] Svensk `noStations` (var engelska i PHP)
- [x] Samordna `MRT_journey_wizard_l10n_*` med `labels` (samma formulering)
- [x] Vue-fallbacks = PHP-standardtexter
- [x] `defaultTrainType` i wizard-l10n
- [ ] `ticketCta` – koppla i sammanfattning om `ticketUrl` sätts (senare)

## Fas 2 – Månadskalender

- [x] Använd `legendCountHint` och `legendClickHint` i `MrtLegend`
- [x] Ta bort oanvänd `monthLegendHints.ts` om tom

## Fas 3 – Tidtabellsöversikt

- [x] PHP: `ovPrintKey*`, `ovDeviation*`, `ovDeparturesAria`, gren-tabell
- [x] `overviewUiLabels()` + props till `MrtOverview*`

## Fas 4 – Delat

- [x] `MrtAsyncState` – ingen hårdkodad svenska som default
- [x] `legVehicleLabel` – `defaultTrainType` från cfg

## Fas 5 – Admin (pågår)

- [x] `MRT_admin_vue_script_localization()` i `inc/assets/admin-vue.php`
- [x] `adminStr()` + `frontend/vue/src/admin/utils/adminLabels.ts`
- [x] Navigering, `AdminLoadState`, Inställningar, Priser
- [ ] Övriga admin-sidor (översikt, stationer, tidtabeller, m.fl.)

## Underhåll

Efter PHP-ändringar: kör `powershell -File .\scripts\make-i18n.ps1` (WP-CLI + msgmerge + fyll svenska `msgstr`).  
`inc/assets/frontend.php` använder literal `'museum-railway-timetable'` så WP-CLI hittar strängarna (variabeln `MRT_TEXT_DOMAIN` plockas inte upp av `make-pot`).

Se även [STYLE_GUIDE.md](STYLE_GUIDE.md) §5 Översättning.
