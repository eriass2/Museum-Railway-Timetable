# Plan — implementera tester

**Datum:** 2026-06 (senast uppdaterad 2026-06-09)  
**Syfte:** Prioriterad plan för att täta kända luckor i testningen utan att skriva om det som redan fungerar.

**Relaterat:** [DEVELOPER.md](DEVELOPER.md), [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md), [ARCHITECTURE.md](ARCHITECTURE.md) §3, [REBUILD_RULES.md](REBUILD_RULES.md) §7, [frontend/vue/TESTING.md](../frontend/vue/TESTING.md), [TODO.md](TODO.md).

---

## Statusöversikt

| ID | Uppgift | Status |
|----|---------|--------|
| A1 | PHPUnit-deprecations (`WP_Post`-stub) | **Klar** |
| A2 | Docker-rutin i team | Process — `.\scripts\check.ps1 -Vue` |
| A3 | Trafikmeddelanden PHPUnit | **Klar** |
| B1 | Wizard-shortcode PHPUnit | **Klar** |
| B2 | E2E import/export | **Klar** |
| B3 | E2E inställningar | **Klar** |
| B4 | E2E utökad tidtabellseditor | **Klar** |
| B5 | Vitest trafikmeddelanden (admin) | **Klar** |
| B6 | E2E trafikmeddelanden (admin + WP) | **Klar** |
| C1–C3 | Kodtäckning, a11y-scan, prestanda | Valfritt |

**Nästa steg:** Tier C (valfritt) — kodtäckning, a11y-scan, wizard prestanda-baseline.

---

## Nuläge (kort)

| Lager | Verktyg | Status |
|-------|---------|--------|
| PHP domän/REST | PHPUnit (`tests/Unit/`) | Stark — reseplanerare, priser, CSV, REST, dashboard, **trafikmeddelanden** |
| Vue logik | Vitest (`frontend/vue/tests/`) | Bra — composables, utils, wizard, admin, **trafikmeddelanden**, **datetime** |
| E2E | Playwright (`frontend/vue/e2e/`) | Delvis — mount + admin (dashboard, nav, priser, tidtabell, **trafikmeddelanden**) |
| WordPress-integration | Docker + manuell smoke | Utanför PHPUnit (stubs, inte full WP) |
| CI | `.github/workflows/ci.yml` | `composer check`, `vue:check`, Playwright + `ci-e2e-wp.sh` |

**Senast verifierat (Docker):** PHPUnit **456** tester, Vitest **245** tester (71 filer).

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
| A1 | Fixa PHPUnit-deprecations | `tests/wp-stubs.php` | **Klar** | `$post_title`, `$post_status`, `$post_name` på `WP_Post`; central `MRT_is_development_mode()` + fungerande `add_filter` i stubs. |
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

**Ev. komplettering (låg prioritet):** aggregate med tur-avvikelser i samma payload (kräver tung WP-fixture).

### A3 / REST — komplettering (2026-06-09)

| Testfil | Tillagt |
|---------|---------|
| `RestTrafficNoticesTest.php` | GET payload, days clamp, admin GET, 400/validation |
| `RestPermissionsTest.php` | `can_edit_operations` nekas utan capability; publik nonce för traffic-notices |
| `frontend/vue/tests/trafficNotices.test.ts` | Query params för publikt fetch-lager |

---

## Tier B — Täta största luckorna

**Mål:** Automatisera det som idag mest förlitar sig på manuell admin-rökning.

### B1 — Wizard-shortcode (PHPUnit) — **KLAR**

**Levererat:** `tests/Unit/JourneyWizardShortcodeTest.php` — defaults, bool, URL/titel, debug, resolve by title, Vue-mount.

### B2 — E2E: Import/export (admin) — **KLAR**

**Levererat:** `frontend/vue/e2e/admin-import-export.spec.ts` — merge-import av `testdata/fixtures/lennakatten.zip` (WP-Docker). Körs i `ci-e2e-wp.sh`.

---

### B3 — E2E: Inställningar (admin) — **KLAR**

**Levererat:** `frontend/vue/e2e/admin-settings.spec.ts` (statisk mount) + mutable settings-fixture i `e2e/fixtures/admin-rest.mjs`.

---

### B4 — E2E: Utökad tidtabellseditor — **KLAR**

**Levererat:** utökad `frontend/vue/e2e/admin-timetable-flow.spec.ts` — redigera tågnummer, lägg till avvikelse, spara avvikelser.

---

### B5 — Vitest: trafikmeddelanden (admin) — **KLAR**

**Levererat:** `frontend/vue/tests/trafficNoticesAdmin.test.ts` — synlighet, sortering, draft, reorder, renumber.

**Levererat:** `frontend/vue/tests/trafficNotices.test.ts` — publikt REST-anrop (query params).

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
C1–C3 (valfritt)
```

| Tier | Insats (uppskattning) | Vem |
|------|------------------------|-----|
| C | Löpande | Valfritt |

Tier A och B är **klara** (2026-06-09).

---

## Referens — befintliga mönster

| Typ | Exempel att kopiera |
|-----|---------------------|
| PHP domän | `tests/Unit/PriceRulesTest.php` |
| PHP REST | `tests/Unit/RestAdminHandlersTest.php` |
| PHP shortcode | `tests/Unit/TimetableOverviewShortcodeTest.php` |
| PHP wizard shortcode | `tests/Unit/JourneyWizardShortcodeTest.php` |
| PHP trafikmeddelanden | `tests/Unit/TrafficNoticesDomainTest.php` |
| Vitest composable | `frontend/vue/tests/useStopTimes.test.ts` |
| Vitest admin utils | `frontend/vue/tests/trafficNoticesAdmin.test.ts` |
| Vitest traffic notices API | `frontend/vue/tests/trafficNotices.test.ts` |
| Vitest delad datetime | `frontend/vue/tests/datetime.test.ts` |
| E2E admin (WP) | `frontend/vue/e2e/admin-timetable-flow.spec.ts` |
| E2E admin import | `frontend/vue/e2e/admin-import-export.spec.ts` |
| E2E admin settings | `frontend/vue/e2e/admin-settings.spec.ts` |
| E2E admin trafikmeddelanden | `frontend/vue/e2e/admin-traffic-notices.spec.ts` |
| E2E mount (utan WP) | `frontend/vue/e2e/wizard-mount.spec.ts` |
| E2E shortcode (WP) | `frontend/vue/e2e/traffic-notices-wp.spec.ts` |

---

## Statuslogg

| Datum | Tier | Notering |
|-------|------|----------|
| 2026-06-08 | — | Plan skapad. PHPUnit ~441, Vitest 229. Deprecations i `WP_Post`-stub. |
| 2026-06-09 | A3, B5, B6 | Trafikmeddelanden: PHPUnit (domän/REST/shortcode), Vitest admin, E2E admin + WP. Vitest 243. |
| 2026-06-09 | A1, B1–B4 | WP_Post-stubs, wizard shortcode PHPUnit, E2E import/settings/tidtabell. PHPUnit 448, 0 deprecations. |
| 2026-06-09 | REST + Vitest | Utökad `RestTrafficNoticesTest`, permissions, `trafficNotices.test.ts`. PHPUnit 456, Vitest 245. |

Uppdatera tabellen när en tier är klar.
