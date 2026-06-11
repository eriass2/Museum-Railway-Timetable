# TODO – teknisk skuld och förbättringar

Kort lista över **öppna** punkter. Genomförda arbeten finns i respektive plan/doc (t.ex. [TEST_IMPLEMENTATION_PLAN.md](TEST_IMPLEMENTATION_PLAN.md), [WIZARD_PERFORMANCE_PLAN.md](WIZARD_PERFORMANCE_PLAN.md)).

---

## Stopptider — schema v3 (tidtabell B + anslag)

**Status:** fas 4 klar (2026-06-10) — se [STOP_TIME_V3_IMPLEMENTATION.md](STOP_TIME_V3_IMPLEMENTATION.md), [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md), [STOP_TIME_CA.md](STOP_TIME_CA.md)

- [ ] Dev-reset + omimport efter fas 4 i Docker-miljö

---

## Reseplanerare — feedback-widget (v2)

**Status:** v1 klar (2026-06-10) — se [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md)

- [ ] E-postnotis (`wp_mail`) till konfigurerbar adress vid ny rapport
- [ ] Export feedback till CSV

---

## Tidtabellsöversikt — sammanslagna kolumner vid tågbyte

**Status:** kärna klar (2026-06-10), commit `9a44dda` — manuell PDF-validering + polish kvar  
**Varför:** Continuation-turer (t.ex. 64, 74 från Marielund) ska visas i samma kolumn som ankommande tåg, inte som egna tomma kolumner.

**Nyckelfiler:** `overview-column-merge.php`, `overview-rail-columns.php`, `overview-rail-cells.php`, `overview-rail-rows.php`, `tests/Unit/OverviewColumnMergeTest.php`

- [ ] E2E/admin: redigering efter Marielund sparar rätt `serviceId`
- [ ] **Manuell check:** GRÖN tidtabell 2026-06-06 — Faringe→Uppsala 5 kolumner (70, 60, 62, 96, 78); tur 71 ut utan kolumn 61; Marielund ankomst/avgång i samma kolumn
- [ ] Kör `.\scripts\docker-dev-reset.ps1 -SkipCompose` om lokal miljö visar gammalt

---

## Tidtabellsöversikt — buss vid knutpunkt (Selknä m.fl.)

**Status:** fix klar (2026-06-10), commit `8c8a30a` — manuell validering kvar  
**Nyckelfiler:** `overview-bus-junction.php`, `overview-bus-stops.php`, `overview-rail-rows.php`

- [ ] **Manuell check:** Selknä inbound GRÖN — tur 62 → B3/Fjällnora, tur 96 → B4/Fjällnora; inga delade rader med korsade tider
- [ ] **Manuell check:** Selknä inbound RÖD (söndag) — Linnés Hammarby-buss (B5) visas; **inte** på gröna bussdagar
- [ ] Thun's-expressen vertikal etikett — layout i `MrtOverviewRailGroupGrid.vue` / CSS

---

## Genomfört (arkiv)

| Område | Klar | Referens |
|--------|------|----------|
| Vue — gemensamma datetime/tid-utils | 2026-06-09 | [VUE_UTILS.md](VUE_UTILS.md), `frontend/vue/src/utils/datetime.ts` |
| PHP — utils-snabbguide i dokumentation | 2026-06-10 | [STYLE_GUIDE.md](STYLE_GUIDE.md) |
| Reseplanerare — feedback-widget v1 | 2026-06-10 | [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md), [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) J13 |
| Tidtabellsöversikt — typografi (Turvy) | 2026-06-10 | `frontend/vue/src/styles/timetable-overview.css`, commit `6d91237` |
| Tidtabellsöversikt — kolumnsammanslagning (kärna) | 2026-06-10 | commit `9a44dda`, PHPUnit + vue-check gröna |
| Tidtabellsöversikt — bussrader per tågkolumn (Selknä) | 2026-06-10 | commit `8c8a30a`, `TimetableOverviewHelpersTest` |
