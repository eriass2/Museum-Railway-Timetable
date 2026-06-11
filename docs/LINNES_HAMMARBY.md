# Linnés Hammarby — data och import

Guide för att få in **bussgrenen Selknä ↔ Linnés Hammarby** i admin och reseplanerare (Jesper beta D17).

## Bakgrund

Stationen fanns i fixturen men saknade rutt, turer och stopptider — därför visade reseplaneraren inga resor (J6). Grenen är **inte** i Anslagstidtabell-PDF:ens Fjällnora-bussar; tider i repot speglar Selknä-avgångar parallellt med Fjällnora-bussarna tills operatören bekräftar officiella tider.

**Trafikdagar:** Linnés Hammarby-bussen kör **bara söndagar** (röda trafikdagar) inom bussfönstret 1 juli–16 augusti — inte på gröna bussdagar (lördag/ons/tors).

**Domän (spikad):** Linnés hör till **Marielund** (shuttle + byte med tåg). Från Linnés går buss till **Marielund** eller direkt **Uppsala** (inga byten). **Fjällnora** (Selkné) är en **annan** gren — se [LINES_REFACTOR_PLAN.md](LINES_REFACTOR_PLAN.md).

## Dev / test (fixture i repo)

| Fil | Innehåll |
|-----|----------|
| `routes.csv` | Linnés-shuttles med `branch_code=linnes-hammarby` (Selkné-, Marielund- och Uppsala-ben) |
| `services.csv` | B9–B14 under `red-buss` (ingen Selkné–Linnés-buss; se [LINES_REFACTOR_PLAN.md](LINES_REFACTOR_PLAN.md)) |
| `stoptimes.csv` | Selknä ↔ Linnés Hammarby |
| `timetable_dates.csv` | Samma söndagar som `red` inom 1/7–16/8 |

Efter ändring:

```powershell
composer csv:validate -- testdata/fixtures/lennakatten
composer csv:zip
.\scripts\test.ps1 tests/Unit/LennakattenJourneySearchTest.php
```

Dev-reset importerar hela paketet: `.\scripts\docker-dev-reset.ps1`

## Staging / produktion (CSV-import)

1. **Exportera** uppdaterat paket lokalt:
   ```powershell
   composer csv:zip
   ```
   Zip: `testdata/fixtures/lennakatten.zip`

2. **WordPress admin** → **Import/export** (`#/import-export`)

3. **Ladda upp** zip-filen → **Validera** (inga fel)

4. Välj **Merge** om befintlig Lennakatten-data ska behållas och bara nya rader läggas till, eller **Override** på tom/staging-miljö

5. **Importera** → kontrollera:
   - Stationer & rutter: grenen syns
   - Tidtabell `RÖD ANSLUTNINGSBUSS 2026 (Linnés Hammarby)`: bussar B9–B14
   - Reseplanerare: Uppsala Östra → Linnés Hammarby på **röd söndag** (t.ex. 2026-07-05)

6. Verifiera **buss/tåg-koppling** vid Selknä mot Fjällnora-grenen (A10 / B2) — Fjällnora-bussar ska **inte** visas på samma dag som Linnés om de inte trafikerar

## Manuell inmatning (alternativ)

Se [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) — avsnitt *Ny bussgren*.

## Uppdatera tider

1. Redigera CSV under `testdata/fixtures/lennakatten/` **eller** i admin (Stopptider / rutnät)
2. Exportera från staging via Import/export om sanning ska ligga i repo
3. Kör validering och journey-test ovan
