# Linnés Hammarby — data och import

Guide för att få in **bussgrenen Selknä ↔ Linnés Hammarby** i admin och reseplanerare (Jesper beta D17).

## Bakgrund

Stationen fanns i fixturen men saknade rutt, turer och stopptider — därför visade reseplaneraren inga resor (J6). Grenen är **inte** i Anslagstidtabell-PDF:ens Fjällnora-bussar; tider i repot speglar Selknä-avgångar parallellt med Fjällnora-bussarna tills operatören bekräftar officiella tider.

## Dev / test (fixture i repo)

| Fil | Innehåll |
|-----|----------|
| `routes.csv` | `selkna-linnes-hammarby`, `linnes-hammarby-selkna` |
| `services.csv` | B5, B9–B14 under `green-buss` |
| `stoptimes.csv` | Selknä ↔ Linnés Hammarby |

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
   - Tidtabell `GRÖN ANSLUTNINGSBUSS 2026`: bussar B5/B9–B14
   - Reseplanerare: Uppsala Östra → Linnés Hammarby på grön bussdag (t.ex. 2026-07-04)

6. Verifiera **buss/tåg-koppling** vid Selknä mot Fjällnora-grenen (A10 / B2)

## Manuell inmatning (alternativ)

Se [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) — avsnitt *Ny bussgren*.

## Uppdatera tider

1. Redigera CSV under `testdata/fixtures/lennakatten/` **eller** i admin (Stopptider / rutnät)
2. Exportera från staging via Import/export om sanning ska ligga i repo
3. Kör validering och journey-test ovan
