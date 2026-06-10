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
