# Åtgärdsplan – översättningar (i18n)

Text domain: `museum-railway-timetable`. Publikt Vue får strängar från PHP (`strings`, `wizard`, `labels`). Admin Vue saknar i18n (fas 5).

## Status

| Fas | Beskrivning | Status |
|-----|-------------|--------|
| 1 | Dokumentation och wizard PHP/Vue | Klar |
| 2 | Månadskalender – legend-hints från config | Planerad |
| 3 | Tidtabellsöversikt – UI-etiketter via `strings` | Planerad |
| 4 | Delade komponenter (`MrtAsyncState`, tåg-fallback) | Planerad |
| 5 | Admin Vue – `mrtAdminVue.strings` | Ej påbörjad |

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

## Fas 5 – Admin (senare)

- [ ] `MRT_admin_vue_script_localization()` i `inc/assets/admin-vue.php`
- [ ] Ersätt hårdkodad text i `frontend/vue/src/admin/**` stegvis (sida för sida)

## Underhåll

Efter PHP-ändringar: uppdatera `languages/museum-railway-timetable.pot` (WP-CLI `wp i18n make-pot` eller manuellt).

Se även [STYLE_GUIDE.md](STYLE_GUIDE.md) §5 Översättning.
