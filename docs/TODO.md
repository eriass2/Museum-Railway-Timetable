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

**Status:** fas 2 klar (2026-06-10) — se [STOP_TIME_V3_IMPLEMENTATION.md](STOP_TIME_V3_IMPLEMENTATION.md)

**Beslutat (2026-06-10):** se [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md)

**Kvar (fas 3):**

- [ ] Anslag-overlay automatiskt i sync (Ca + `in_service_timetable` där B saknar rad)
- [ ] Dev-reset + omimport efter schema-byte i Docker-miljö

---

## PHP – utils-snabbguide i dokumentation

**Status:** planerad  
**Varför:** Vue har [VUE_UTILS.md](VUE_UTILS.md); PHP:s motsvarande helpers (`datetime.php`, `log.php`, `helpers-utils.php`, `request-params.php`) nämns bara sporadiskt i [ARCHITECTURE.md](ARCHITECTURE.md).

**Mål:** Kort pekartabell (“när använder jag vad”) i [STYLE_GUIDE.md](STYLE_GUIDE.md) eller [ARCHITECTURE.md](ARCHITECTURE.md):

| Modul | Användning |
|-------|------------|
| `inc/domain/datetime/datetime.php` | Validering och beräkning av datum/tid (ISO, HH:MM) |
| `inc/infrastructure/wordpress/log.php` | Dev-loggning (`MRT_log`, bakom `WP_DEBUG`) |
| `inc/infrastructure/wordpress/helpers-utils.php` | Alerts, post-uppslag, tidtabellsvisning (P/A/X, HH.MM) |
| `inc/domain/journey/request-params.php` | REST-body-validering för reseplanerare |

**Acceptanskriterier:**

- [ ] Tabell + kort intro tillagd i vald doc-fil
- [ ] Korslänk från [VUE_UTILS.md](VUE_UTILS.md) eller [ARCHITECTURE.md](ARCHITECTURE.md) så PHP- och Vue-guiden hittas parallellt
- [ ] Ingen kodändring — enbart dokumentation

---

## Reseplanerare — feedback-widget (FAB)

**Status:** planerad (beslut 2026-06-10)  
**Plan:** [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md)  
**Relaterat:** J13 / D2 i [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md)

Besökare rapporterar buggar/förslag via flytande knapp i reseplaneraren; sparas i WordPress (`mrt_feedback` CPT). Oberoende av beta-banner — båda styrs i admin.

**Beslut:** e-post valfri; GDPR-text i panel; e-postnotis till team → v2.

### Fas 1 — Backend + admin-inställning

- [ ] `wizard_feedback_enabled` i `mrt_settings` + REST + SettingsPage
- [ ] CPT `mrt_feedback` (private) + meta-fält
- [ ] `POST /mrt/v1/wizard/feedback` (nonce, rate limit, honeypot)
- [ ] CSV import/export av `wizard_feedback_enabled`

### Fas 2 — Publik Vue-widget

- [ ] `WizardFeedbackFab.vue` + `WizardFeedbackPanel.vue` + CSS
- [ ] `useWizardFeedback.ts` — submit + tack-meddelande
- [ ] GDPR-text i panelen
- [ ] Automatisk kontext (sida, steg, route snapshot)
- [ ] Vitest/SSR-tester

### Fas 3 — Admin-lista

- [ ] REST: lista, detalj, PATCH status
- [ ] Admin-sida Feedback (meny eller under Dashboard)
- [ ] Status: Ny / Läst / Åtgärdad / Avvisad

### Framtida utveckling (v2)

- [ ] E-postnotis (`wp_mail`) till konfigurerbar adress vid ny rapport
- [ ] Export feedback till CSV

---

## Tidtabellsöversikt — sammanslagna kolumner vid tågbyte

**Status:** planerad (2026-06-10)  
**Varför:** Turer som bara avgår efter bytesstation (t.ex. 64, 74 från Marielund) visas som egna, mest tomma kolumner längst till vänster. PDF-referensen visar dem som fortsättning i samma kolumn som ankommande tåg (60→74, 96→64). Resesökningsmotorn och datamodellen (separata tjänster + stopptider) är redan korrekta — felet är presentationslogiken.

**Befintligt stöd:** `train_change_map` per station (`mrt_station_train_change_map`), admin (`StationTrainChangeEditor.vue`), CSV (`station_train_changes.csv`). Används idag **endast** för tågbyte-raden, inte för kolumnsammanslagning.

**Mål:** PDF-lik vy — en kolumn = en resa genom bytespunkten. Tjänster markerade som `to_service` i kartan döljs som egna kolumner; övre segment (→ Marielund) visar parent, nedre segment (Marielund →) visar continuation.

**Relaterat:** Analys i chat 2026-06-10; Lennakatten-fixture `testdata/fixtures/lennakatten/station_train_changes.csv` (60→74, 96→64); [CSV_FORMAT.md](CSV_FORMAT.md) §4.1b; [OPERATOR_ONBOARDING.md](OPERATOR_ONBOARDING.md) § Tågbyte.

### Fas 0 — Sorteringsfix (valfri, kan köras separat)

- [ ] I `MRT_sort_timetable_services_by_first_station_time` (`group-view.php`): tom avgång vid route-start → sortera **sist**, inte först

### Fas 1 — PHP: kolumn-sammanslagning (kärna)

Ny modul `inc/domain/timetable/view/overview/overview-column-merge.php`:

- [ ] Bygg omvänd karta från `train_change_map`: `{ '74' => '60', '64' => '96' }`
- [ ] Exkludera continuation-only tjänster från kolumnlistan
- [ ] `display_columns` med `primary_idx` + valfri `continuation_idx`
- [ ] Sortera kolumner på parent-tid vid första stationen (tom tid sist)
- [ ] Per rad: välj rätt tjänst — före/inkl. ankomst Marielund → primary; efter avgång Marielund → continuation
- [ ] Integrera i `group-view.php`, `overview-rail-columns.php`, `overview-rail-cells.php`, `overview-rail-rows.php`
- [ ] `require_once` i `inc/bootstrap/domain.php`
- [ ] Utan konfigurerad map: oförändrat beteende (fail empty)

### Fas 2 — JSON-kontrakt

- [ ] `TimetableOverviewColumn.continuation?` — `{ serviceId, serviceNumber, trainTypeName, iconKey }`
- [ ] `TimetableTimeCell.serviceId?` — vilken tjänst cellen tillhör (krävs för inline-redigering i sammanslagen kolumn)
- [ ] Bakåtkompatibelt: publik vy kan ignorera nya fält

### Fas 3 — Vue: admin-redigering

- [ ] `types/timetableOverview.ts` — nya fält
- [ ] `EditableOverviewRailGroup.vue` — använd `cell.serviceId ?? column.serviceId` i `OverviewGridCellEditor`
- [ ] Publik `MrtOverviewRailGroupGrid.vue` — ingen ändring (huvud visar primary)

### Fas 4 — Admin & validering (polish)

- [ ] Hint i `StationTrainChangeEditor.vue`: continuation-turer visas inte som egna kolumner
- [ ] Valfri validering vid spara: saknad `to_service`, dubbelkoppling

### Fas 5 — Tester

Ny `tests/Unit/OverviewColumnMergeTest.php` (Lennakatten-scenario):

- [ ] 7 tjänster Faringe→Uppsala → 5 kolumner (64, 74 dolda)
- [ ] Kolumnordning: 70, 60, 62, 96, 78 — inte 64/74 först
- [ ] Marielund ankomst från primary (60: 10.20); avgång från continuation (74: 11.45)
- [ ] Tågbyte-rad: 74 under kolumn 60, 64 under kolumn 96
- [ ] Inkoming riktning: 71→61 vid Marielund
- [ ] Tom map → en kolumn per tjänst (fallback)
- [ ] E2E/admin: redigering efter Marielund sparar rätt `serviceId`

**Acceptanskriterier:**

- [ ] GRÖN tidtabell Faringe→Uppsala matchar PDF-kolumnantal och ordning
- [ ] Resesökningsmotorn opåverkad (ingen datamodelländring)
- [ ] `.\scripts\check.ps1` och `.\scripts\vue-check.ps1` gröna

**Uppskattad insats:** ~1–1,5 dag (fas 1+5 tillsammans, sedan fas 3, fas 0/4 valfritt).

**Medvetet utanför scope (v1):** auto-detektera koppling utan map; dubbelt tågnummer i kolumnhuvud; ändringar i resesökningsmotorn.
