# TODO – teknisk skuld och förbättringar

Kort lista över planerade förbättringar som inte är aktiva utvecklingsplaner (de ligger i t.ex. [TEST_IMPLEMENTATION_PLAN.md](TEST_IMPLEMENTATION_PLAN.md), [WIZARD_PERFORMANCE_PLAN.md](WIZARD_PERFORMANCE_PLAN.md)).

---

## Vue – gemensamma datetime/tid-utils

**Status:** planerad  
**Varför:** PHP har en central modul (`inc/domain/datetime/datetime.php`); Vue har samma logik utspridd på flera ställen med lite olika beteende.

**Mål:** Skapa `frontend/vue/src/utils/datetime.ts` (eller liknande) med:

- `validateYmd()` / `validateHhmm()` — spegla PHP:s `MRT_validate_date` / `MRT_validate_time_hhmm`
- `hhmmToMinutes()` / `minutesToHhmm()` — ersätt duplicering i `tripClock.ts`, `settingsTime.ts`, `useOverviewGridEdit.ts`
- `todayYmd()` — flytta från privat helper i `trafficNoticesAdmin.ts`
- `formatYmdForDisplay()` — flytta från `wizard/utils/wizardDate.ts` till delad `utils/` om den ska användas utanför wizard
- `formatHhmmForDisplay()` (kolon → punkt, motsvarar PHP:s `MRT_format_time_display`) — flytta från `tripClock.ts` `formatTripClock` till delad kärna; `tripClock` blir tunt lager
- Behåll domänspecifika wrappers (`settingsTime` för HTML `<input type="time">`, `tripClock` för resvisning) som tunna lager ovanpå kärnan

**Acceptanskriterier:**

- [ ] Nya Vitest-tester för delade funktioner
- [ ] Befintliga tester (`wizardDate`, `settingsTime`, `useOverviewGridEdit`) gröna
- [ ] Uppdatera [VUE_UTILS.md](VUE_UTILS.md) med pekare till nya modulen
- [ ] Ingen beteendeförändring i produktion (refaktor, inte feature)

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
