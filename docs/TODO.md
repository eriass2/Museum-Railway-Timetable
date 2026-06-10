# TODO – teknisk skuld och förbättringar

Kort lista över planerade förbättringar som inte är aktiva utvecklingsplaner (de ligger i t.ex. [TEST_IMPLEMENTATION_PLAN.md](TEST_IMPLEMENTATION_PLAN.md), [WIZARD_PERFORMANCE_PLAN.md](WIZARD_PERFORMANCE_PLAN.md)).

---

## Vue – gemensamma datetime/tid-utils

**Status:** genomförd (2026-06-09)  
**Varför:** PHP har en central modul (`inc/domain/datetime/datetime.php`); Vue har samma logik utspridd på flera ställen med lite olika beteende.

**Mål:** Skapa `frontend/vue/src/utils/datetime.ts` (eller liknande) med:

- `validateYmd()` / `validateHhmm()` — spegla PHP:s `MRT_validate_date` / `MRT_validate_time_hhmm`
- `hhmmToMinutes()` / `minutesToHhmm()` — ersätt duplicering i `tripClock.ts`, `settingsTime.ts`, `useOverviewGridEdit.ts`
- `todayYmd()` — flytta från privat helper i `trafficNoticesAdmin.ts`
- `formatYmdForDisplay()` — flytta från `wizard/utils/wizardDate.ts` till delad `utils/` om den ska användas utanför wizard
- `formatHhmmForDisplay()` (kolon → punkt, motsvarar PHP:s `MRT_format_time_display`) — flytta från `tripClock.ts` `formatTripClock` till delad kärna; `tripClock` blir tunt lager
- Behåll domänspecifika wrappers (`settingsTime` för HTML `<input type="time">`, `tripClock` för resvisning) som tunna lager ovanpå kärnan

**Acceptanskriterier:**

- [x] Nya Vitest-tester för delade funktioner (`datetime.test.ts`)
- [x] Befintliga tester (`wizardDate`, `settingsTime`, `useOverviewGridEdit`) gröna
- [x] Uppdatera [VUE_UTILS.md](VUE_UTILS.md) med pekare till nya modulen
- [x] Ingen beteendeförändring i produktion (refaktor, inte feature)

**Relaterat:** Analys i chat 2026-06-09; PHP-referens i `tests/Unit/HelpersDatetimeTest.php`.

---

## Stopptider — schema v3 (tidtabell B + anslag)

**Status:** fas 4 klar (2026-06-10) — se [STOP_TIME_V3_IMPLEMENTATION.md](STOP_TIME_V3_IMPLEMENTATION.md)

**Beslutat (2026-06-10):** se [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md), [STOP_TIME_CA.md](STOP_TIME_CA.md)

### Fas 4 — Ca/visning ✓


- [ ] Dev-reset + omimport efter fas 4 i Docker-miljö

---

## PHP – utils-snabbguide i dokumentation

**Status:** genomförd (2026-06-10)  
**Varför:** Vue har [VUE_UTILS.md](VUE_UTILS.md); PHP:s motsvarande helpers (`datetime.php`, `log.php`, `helpers-utils.php`, `request-params.php`) nämns bara sporadiskt i [ARCHITECTURE.md](ARCHITECTURE.md).

**Mål:** Kort pekartabell (“när använder jag vad”) i [STYLE_GUIDE.md](STYLE_GUIDE.md) eller [ARCHITECTURE.md](ARCHITECTURE.md):

| Modul | Användning |
|-------|------------|
| `inc/domain/datetime/datetime.php` | Validering och beräkning av datum/tid (ISO, HH:MM) |
| `inc/infrastructure/wordpress/log.php` | Dev-loggning (`MRT_log`, bakom `WP_DEBUG`) |
| `inc/infrastructure/wordpress/helpers-utils.php` | Alerts, post-uppslag, tidtabellsvisning (P/A/X, HH.MM) |
| `inc/domain/journey/request-params.php` | REST-body-validering för reseplanerare |

**Acceptanskriterier:**

- [x] Tabell + kort intro tillagd i vald doc-fil
- [x] Korslänk från [VUE_UTILS.md](VUE_UTILS.md) eller [ARCHITECTURE.md](ARCHITECTURE.md) så PHP- och Vue-guiden hittas parallellt
- [x] Ingen kodändring — enbart dokumentation

---

## Reseplanerare — feedback-widget (FAB)

**Status:** v1 implementerad (2026-06-10)  
**Plan:** [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md)  
**Relaterat:** J13 / D2 i [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md)

Besökare rapporterar buggar/förslag via flytande knapp i reseplaneraren; sparas i WordPress (`mrt_feedback` CPT). Oberoende av beta-banner — båda styrs i admin.

**Beslut:** e-post valfri; GDPR-text i panel; e-postnotis till team → v2.

### Fas 1 — Backend + admin-inställning

- [x] `wizard_feedback_enabled` i `mrt_settings` + REST + SettingsPage
- [x] CPT `mrt_feedback` (private) + meta-fält
- [x] `POST /mrt/v1/wizard/feedback` (nonce, rate limit, honeypot)
- [x] CSV import/export av `wizard_feedback_enabled`

### Fas 2 — Publik Vue-widget

- [x] `WizardFeedbackWidget.vue` + CSS
- [x] Submit + tack-meddelande
- [x] GDPR-text i panelen
- [x] Automatisk kontext (sida, steg, route snapshot)
- [x] Vitest/SSR-tester + Playwright-smoke

### Fas 3 — Admin-lista

- [x] REST: lista + PATCH status
- [x] Admin-sida Feedback (meny)
- [x] Status: Ny / Läst / Åtgärdad / Avvisad

### Framtida utveckling (v2)

- [ ] E-postnotis (`wp_mail`) till konfigurerbar adress vid ny rapport
- [ ] Export feedback till CSV

---

## Tidtabellsöversikt — sammanslagna kolumner vid tågbyte

**Status:** kärna klar (2026-06-10), commit `9a44dda` — manuell PDF-validering + polish kvar  
**Varför:** Turer som bara avgår efter bytesstation (t.ex. 64, 74 från Marielund) visas som egna, mest tomma kolumner längst till vänster. PDF-referensen visar dem som fortsättning i samma kolumn som ankommande tåg (60→74, 96→64). Resesökningsmotorn och datamodellen (separata tjänster + stopptider) är redan korrekta — felet var presentationslogiken.

**Befintligt stöd:** `train_change_map` per station (`mrt_station_train_change_map`), admin (`StationTrainChangeEditor.vue`), CSV (`station_train_changes.csv`). Används för tågbyte-raden **och** kolumnsammanslagning (`overview-column-merge.php`).

**Mål:** PDF-lik vy — en kolumn = en resa genom bytespunkten. Tjänster markerade som `to_service` i kartan döljs som egna kolumner; övre segment (→ Marielund) visar parent, nedre segment (Marielund →) visar continuation.

**Relaterat:** Lennakatten-fixture `testdata/fixtures/lennakatten/station_train_changes.csv` (60→74, 96→64, 71→61, 63→97); [CSV_FORMAT.md](CSV_FORMAT.md) §4.1b; [OPERATOR_ONBOARDING.md](OPERATOR_ONBOARDING.md) § Tågbyte.

**Nyckelfiler:** `inc/domain/timetable/view/overview/overview-column-merge.php`, `overview-rail-columns.php`, `overview-rail-cells.php`, `overview-rail-rows.php`, `tests/Unit/OverviewColumnMergeTest.php`.

### Fas 0 — Sorteringsfix ✓

- [x] I `MRT_sort_timetable_services_by_first_station_time` (`group-view.php`): tom avgång vid route-start → sortera **sist**

### Fas 1 — PHP: kolumn-sammanslagning (kärna) ✓

- [x] `overview-column-merge.php` — omvänd karta, `display_columns`, per-rad service_idx
- [x] Integrerat i columns/rows/cells + bussrader (primary_idx för busslookup)
- [x] Utan map: en kolumn per tjänst (fallback)
- [ ] `require_once` i `inc/bootstrap/domain.php` — **ej nödvändigt**; laddas via `overview-rail-rows.php`

### Fas 2 — JSON-kontrakt ✓

- [x] `TimetableOverviewColumn.continuation?`
- [x] `TimetableTimeCell.serviceId?`

### Fas 3 — Vue: admin-redigering ✓

- [x] `types/timetableOverview.ts`
- [x] `EditableOverviewRailGroup.vue` — `cell.serviceId ?? column.serviceId`

### Fas 4 — Admin & validering (polish)

- [ ] Hint i `StationTrainChangeEditor.vue`: continuation-turer visas inte som egna kolumner
- [ ] Valfri validering vid spara: saknad `to_service`, dubbelkoppling

### Fas 5 — Tester

- [x] `tests/Unit/OverviewColumnMergeTest.php` — Lennakatten-scenario (enhetstester)
- [ ] E2E/admin: redigering efter Marielund sparar rätt `serviceId`

**Acceptanskriterier (överlämning):**

- [ ] **Manuell check:** GRÖN tidtabell 2026-06-06 — Faringe→Uppsala 5 kolumner (70, 60, 62, 96, 78); tur 71 ut utan kolumn 61; Marielund ankomst/avgång i samma kolumn
- [ ] Kör `.\scripts\docker-dev-reset.ps1 -SkipCompose` om lokal miljö visar gammalt
- [x] Resesökningsmotorn opåverkad (ingen datamodelländring)
- [x] PHPUnit + vue-check gröna (2026-06-10)

**Medvetet utanför scope (v1):** auto-detektera koppling utan map; dubbelt tågnummer i kolumnhuvud; ändringar i resesökningsmotorn.

---

## Tidtabellsöversikt — buss vid knutpunkt (Selknä m.fl.)

**Status:** fix klar (2026-06-10), commit `8c8a30a` — manuell validering kvar  
**Varför:** Bussrader byggdes som **två rader per bussgren** med tider i **alla** tågkolumner samtidigt → staplade “Från Selknä / Till Fjällnora”-rader med blandade tider (tur 62/96/60).

**Lösning:** En avgång/ankomst-radpar **per matchat tåg–buss-par**; sparse celler (`—` i övriga kolumner). Flera grenar (Fjällnora, Linnés Hammarby) sorteras på bussavgång i `MRT_timetable_junction_bus_rows_for_station`.

**Nyckelfiler:** `overview-bus-junction.php`, `overview-bus-stops.php`, `overview-rail-rows.php`; test `TimetableOverviewHelpersTest::test_junction_bus_rows_use_one_pair_per_matched_train`.

**Överlämning:**

- [ ] **Manuell check:** Selknä på inbound GRÖN — tur 60 → B5/Linnés Hammarby, tur 62 → B3/Fjällnora, tur 96 → B4/Fjällnora; inga delade rader med korsade tider
- [ ] Ev. framtida polish: destination i radetikett när flera grenar har samma “Från Selknä”-text (idag skiljs de genom sparse kolumner + sortering)
- [ ] Thun's-expressen vertikal etikett — layout i `MrtOverviewRailGroupGrid.vue` / CSS (ej ändrad i denna fix)

---

## Tidtabellsöversikt — typografi (Turvy)

**Status:** klar (2026-06-10), commits `6d91237` (stationer + tider normal vikt; Ca/P/A/X spacing)

- [x] Stationer och klockslag utan fetstil
- [x] Vue-byggd CSS i `frontend/vue/src/styles/timetable-overview.css`

