# Plan — implementera tester

**Datum:** 2026-06 (senast uppdaterad 2026-06-09)  
**Syfte:** Prioriterad plan för att täta kända luckor i testningen utan att skriva om det som redan fungerar.

**Relaterat:** [DEVELOPER.md](DEVELOPER.md), [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md), [ARCHITECTURE.md](ARCHITECTURE.md) §3, [REBUILD_RULES.md](REBUILD_RULES.md) §7, [frontend/vue/TESTING.md](../frontend/vue/TESTING.md), [TODO.md](TODO.md).

---

## Statusöversikt

| ID | Uppgift | Status |
|----|---------|--------|
| A1 | PHPUnit-deprecations (`WP_Post`-stub) | **Kvar** — 2 deprecations kvar |
| A2 | Docker-rutin i team | Process — `.\scripts\check.ps1 -Vue` |
| A3 | Trafikmeddelanden PHPUnit | **Klar** |
| B1 | Wizard-shortcode PHPUnit | **Kvar** |
| B2 | E2E import/export | **Kvar** |
| B3 | E2E inställningar | **Kvar** |
| B4 | E2E utökad tidtabellseditor | **Kvar** |
| B5 | Vitest trafikmeddelanden (admin) | **Klar** |
| B6 | E2E trafikmeddelanden (admin + WP) | **Klar** |
| C1–C3 | Kodtäckning, a11y-scan, prestanda | Valfritt |

**Nästa steg:** A1 → B1 → B2.

---

## Nuläge (kort)

| Lager | Verktyg | Status |
|-------|---------|--------|
| PHP domän/REST | PHPUnit (`tests/Unit/`) | Stark — reseplanerare, priser, CSV, REST, dashboard, **trafikmeddelanden** |
| Vue logik | Vitest (`frontend/vue/tests/`) | Bra — composables, utils, wizard, admin, **trafikmeddelanden**, **datetime** |
| E2E | Playwright (`frontend/vue/e2e/`) | Delvis — mount + admin (dashboard, nav, priser, tidtabell, **trafikmeddelanden**) |
| WordPress-integration | Docker + manuell smoke | Utanför PHPUnit (stubs, inte full WP) |
| CI | `.github/workflows/ci.yml` | `composer check`, `vue:check`, Playwright + `ci-e2e-wp.sh` |

**Senast verifierat (Docker):** PHPUnit **441** tester, Vitest **243** tester (70 filer).

**Standard:** All körning sker i **Docker** — använd aldrig `-Local` om host-PHP saknas eller är &lt; 8.2.

```powershell
.\scripts\check.ps1 -Vue          # PHP + Vue (rekommenderat före PR)
.\scripts\test.ps1                # PHPUnit
.\scripts\vue-check.ps1           # Vue typecheck + Vitest + build
```

---

## Principer

1. **Testa affärsregler i PHP** (`inc/domain/`) — inte i Vue om regeln redan finns server-side.
2. **PHPUnit utan full WordPress** — snabba enhetstester mot `tests/wp-stubs.php`; WP-verifiering via E2E/smoke.
3. **Vue: testa utils/composables först** — hela `.vue`-sidor bara när de har egen logik eller kritiska flöden.
4. **E2E för admin-flöden** som idag kräver manuell rökning (import, inställningar, editor).
5. **En PR = ett testlager i taget** när möjligt (lättare review).

**Checklista ny funktion** (från [ARCHITECTURE.md](ARCHITECTURE.md)):

1. Logik i `inc/domain/…`
2. Tester i `tests/Unit/` (eller Vitest om ren klientlogik)
3. Tunt lager i shortcode/REST
4. UI visar och skickar parametrar
5. E2E om admin-flöde eller publikt mount med WP-koppling

---

## Tier A — Snabba vinster

**Mål:** Grön CI utan varningar, stabil testinfrastruktur.

| ID | Uppgift | Filer | Status | Acceptanskriterium |
|----|---------|-------|--------|-------------------|
| A1 | Fixa PHPUnit-deprecations | `tests/wp-stubs.php` | **Kvar** | Deklarera `$post_title`, `$post_status` (och ev. `$post_name`) på `WP_Post`. Kör `.\scripts\test.ps1 --display-deprecations` — **0 deprecations**. |
| A2 | Bekräfta Docker-rutin i team | — | Process | Alla PR:er kör `.\scripts\check.ps1 -Vue` i Docker före merge. |
| A3 | Trafikmeddelanden PHPUnit | `tests/Unit/TrafficNotices*.php`, `tests/Unit/RestTrafficNoticesTest.php` | **Klar** | Domän, REST, shortcode — se levererade tester nedan. |

### A1 — teknisk not

PHP 8.2+ varnar när kod sätter **odeklarerade properties** på objekt. Test-stubben `WP_Post` sätter `$post_title` / `$post_status` dynamiskt i REST-tester (t.ex. `RestTimetablesDataTest`). Det är **inte** en produktionsbugg — fix i stubben räcker.

### A3 — levererat (2026-06)

| Testfil | Täcker |
|---------|--------|
| `TrafficNoticesDomainTest.php` | 500-tecken, datumfilter, aggregate, inställd-rad |
| `RestTrafficNoticesTest.php` | Ogiltigt datum, admin PUT sparar meddelanden |
| `TrafficNoticesShortcodeTest.php` | Context defaults, tom HTML, generellt meddelande |

**Ev. komplettering (låg prioritet):** fler REST-felkoder (403), aggregate med tur-avvikelser i samma payload.

---

## Tier B — Täta största luckorna

**Mål:** Automatisera det som idag mest förlitar sig på manuell admin-rökning.

### B1 — Wizard-shortcode (PHPUnit) — **KVAR**

**Varför:** Månad, översikt och index har shortcode-tester; wizard (`inc/public/journey-wizard/shell.php`) saknar motsvarande.

**Ny fil:** `tests/Unit/JourneyWizardShortcodeTest.php`

**Mönster:** Kopiera upplägg från `tests/Unit/TimetableOverviewShortcodeTest.php` — mocka `MRT_render_vue_mount` via `$GLOBALS['mrt_test_vue_mount']`.

| Testfall | Funktion / beteende |
|----------|---------------------|
| Defaults | `MRT_journey_wizard_parse_shortcode_atts([])` — tomma strängar, `embedded === false` |
| Bool-attribut | `MRT_journey_wizard_shortcode_bool('1')`, `'yes'`, `'0'` |
| URL/titel | `ticket_url` escapas, `route_title` trimmas |
| Debug (dev) | `MRT_journey_wizard_sanitize_debug_attr` — tillåten preset vs ogiltig; tom utanför dev-läge |
| Render | `MRT_render_shortcode_journey_wizard` — `app === 'wizard'`, config-nycklar (`timetableId`, `embedded`, …) |

**Kör:**

```powershell
.\scripts\test.ps1 tests/Unit/JourneyWizardShortcodeTest.php
```

---

### B2 — E2E: Import/export (admin) — **KVAR**

**Varför:** CSV-import är affärskritisk; ingen automatiserad admin-täckning.

**Ny fil:** `frontend/vue/e2e/admin-import-export.spec.ts`

**Förutsättning:** WP-Docker (samma som `admin-timetable-flow.spec.ts`) — `MRT_E2E_WP_ADMIN_URL` eller demo-URL.

| Steg | Förväntat |
|------|-----------|
| Logga in admin | `#mrt-admin-app` synlig |
| Navigera `#/import-export` | Sida laddas |
| Ladda upp liten fixture-zip | Success-meddelande / inga fel |
| (Valfritt) Exportera mall | Fil/nedladdning eller bekräftelse |

**Referens:** `frontend/vue/e2e/admin-timetable-flow.spec.ts`, `admin-helpers.ts`, `testdata/fixtures/`.

**Kör mot Docker:**

```powershell
docker compose up -d --build
cd frontend/vue
npm run e2e:install
npm run e2e -- e2e/admin-import-export.spec.ts
```

---

### B3 — E2E: Inställningar (admin) — **KVAR**

**Ny fil:** `frontend/vue/e2e/admin-settings.spec.ts`

| Steg | Förväntat |
|------|-----------|
| `#/settings` | Formulär synligt |
| Ändra ett fält (t.ex. operatörsnamn) | Unsaved-banner om tillämpligt |
| Spara | Success |
| Ladda om route | Värde kvar |

**Komplement (Vitest):** `frontend/vue/tests/settingsTime.test.ts` täcker tid — utöka utils om spar-logik flyttas dit.

---

### B4 — E2E: Utöka tidtabellseditor — **KVAR**

**Fil:** utöka `frontend/vue/e2e/admin-timetable-flow.spec.ts` (serial mode behålls).

| Nytt scenario | Förväntat |
|---------------|-----------|
| Redigera befintlig tur | Fält uppdateras och sparas |
| Lägg till avvikelse | Rad syns i listan |
| Snabb avgång (mobil/desktop) | Panel svarar |

---

### B5 — Vitest: trafikmeddelanden (admin) — **KLAR**

**Levererat:** `frontend/vue/tests/trafficNoticesAdmin.test.ts` — synlighet, sortering, draft, reorder, renumber.

**Ev. kvar (valfritt):** dedikerad test för `frontend/vue/src/api/trafficNotices.ts` (publikt fetch-lager).

---

### B6 — E2E: Trafikmeddelanden — **KLAR**

**Levererat (2026-06):**

| Fil | Täcker |
|-----|--------|
| `frontend/vue/e2e/admin-traffic-notices.spec.ts` | Skapa meddelande, reorder, framtida datum, synlighet på index-sida |
| `frontend/vue/e2e/traffic-notices-wp.spec.ts` | Shortcode mount på demo-sida |

Körs i `scripts/ci-e2e-wp.sh` tillsammans med övrig WP-E2E.

---

## Tier C — Förbättra synlighet (valfritt)

| ID | Uppgift | Hur |
|----|---------|-----|
| C1 | PHP kodtäckning | `docker compose --profile tools run --rm php-test vendor/bin/phpunit --coverage-text` — granska filer med 0 % i `inc/` |
| C2 | axe-playwright | Accessibility-scan på demo-sidor i befintlig WP-E2E (komplement till [ACCESSIBILITY.md](ACCESSIBILITY.md)) |
| C3 | Wizard prestanda-baseline | En Playwright-mätning: tid till kalender/resultat (se [WIZARD_PERFORMANCE_PLAN.md](WIZARD_PERFORMANCE_PLAN.md)) |

Kodtäckning i CI är **inte** krav i v1 — använd som utforskande verktyg.

---

## Löpande rutiner

### Varje PR

```powershell
.\scripts\check.ps1 -Vue
```

- UI-ändring: screenshot eller kort video i PR ([REBUILD_RULES.md](REBUILD_RULES.md) §7).
- Import-ändring: minst ett befintligt CSV/PHPUnit-test ska fortsatt passera.

### Release / större UI

- [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) — manuell rökning i Docker.
- [ACCESSIBILITY.md](ACCESSIBILITY.md) — manuell WCAG (~15–30 min).
- Wizard hela flödet: t.ex. Uppsala Östra → Fjällnora (byte/buss).

### CI (redan aktiv)

`.github/workflows/ci.yml`:

- `composer check` (validate + PHPStan + PHPUnit)
- `composer vue:check`
- Playwright E2E (mount + `ci-e2e-wp.sh`)

**WP-E2E i CI** (`scripts/ci-e2e-wp.sh`): overview, month, wizard, index, traffic-notices, admin dashboard/nav/traffic-notices/timetable-flow.

---

## Prioriteringsordning

```
A1 → B1 → B2 → B3 → B4 → C1–C3
```

| Tier | Insats (uppskattning) | Vem |
|------|------------------------|-----|
| A1 | 0,5 h | Backend |
| B1 | 0,5 dag | Backend |
| B2–B4 | 2–4 dagar | Frontend + E2E |
| C | Löpande | Valfritt |

Trafikmeddelanden (A3, B5, B6) är **klara** — ta inte upp dem igen om inte ny funktion läggs till.

---

## Referens — befintliga mönster

| Typ | Exempel att kopiera |
|-----|---------------------|
| PHP domän | `tests/Unit/PriceRulesTest.php` |
| PHP REST | `tests/Unit/RestAdminHandlersTest.php` |
| PHP shortcode | `tests/Unit/TimetableOverviewShortcodeTest.php` |
| PHP trafikmeddelanden | `tests/Unit/TrafficNoticesDomainTest.php` |
| Vitest composable | `frontend/vue/tests/useStopTimes.test.ts` |
| Vitest admin utils | `frontend/vue/tests/trafficNoticesAdmin.test.ts` |
| Vitest delad datetime | `frontend/vue/tests/datetime.test.ts` |
| E2E admin (WP) | `frontend/vue/e2e/admin-timetable-flow.spec.ts` |
| E2E admin trafikmeddelanden | `frontend/vue/e2e/admin-traffic-notices.spec.ts` |
| E2E mount (utan WP) | `frontend/vue/e2e/wizard-mount.spec.ts` |
| E2E shortcode (WP) | `frontend/vue/e2e/traffic-notices-wp.spec.ts` |

---

## Statuslogg

| Datum | Tier | Notering |
|-------|------|----------|
| 2026-06-08 | — | Plan skapad. PHPUnit ~441, Vitest 229. Deprecations i `WP_Post`-stub. |
| 2026-06-09 | A3, B5, B6 | Trafikmeddelanden: PHPUnit (domän/REST/shortcode), Vitest admin, E2E admin + WP. Vitest 243. A1 kvar (2 deprecations). |
| | B1–B4 | Wizard-shortcode PHPUnit + import/settings/editor E2E — **nästa**. |

Uppdatera tabellen när en tier är klar.
