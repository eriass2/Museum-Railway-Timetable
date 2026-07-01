# Feedback – Turvy (tidtabellsöversikt), juni 2026

Återkoppling från Erik efter granskning av genererade tidtabeller (Turvy / `TimetableOverviewApp`). Relaterat till tryckta tidtabeller och Jespers granskning [2026-06-01](2026-06-01-granskning.md) (J1).

**Senast uppdaterad:** 2026-06-30  
**Status:** dokumenterat — **ingen implementation ännu** (post-beta / efter reseplanerare omgång 4)

---

## Sammanfattning

| # | Punkt | Typ | Prioritet | Status |
|---|-------|-----|-----------|--------|
| T1 | Ca, P, X för små — hellre `P 10.03` som tryckt | UX | hög | öppen |
| T2 | Thun's-expressen otydlig — text under tågtyp i kolumnhuvud | UX | medel | öppen |
| T3 | Färger — blekt tidtabellsfärg på stationskolumn; undvik blå/gult | UX | medel | öppen |
| T4 | Busstider i samma tabell / tåg↔buss-koppling | produkt + UX | låg–medel | öppen |
| T5 | Ca vid Bärby tur 93 trots fast uppehåll | **data** (ev.) | utred | se nedan |

**Kort tolkning:** Reseplanerare-omgången (J19–J26) var **ren UX/UI** — inga fixture- eller CSV-ändringar. Turvy-punkterna T1–T3 är också UX. T5 kan vara data om tryckt tabell saknar Ca.

---

## T1. Ca, P och X — läsbarhet

- **Nuvarande:** `MrtOverviewTimeDisplay.vue` — Ca och P/A/X i ~50 % storlek (`--mrt-ov-footnote-size`).
- **Önskat:** Prefix i normal storlek som tryckt — `P 10.03`, `Ca 10.09` (inte litet suffix efter tiden).
- **Kod:** `frontend/vue/src/components/overview/MrtOverviewTimeDisplay.vue`, `overviewTimeDisplay.ts`
- **Doc-mål:** [STOP_TIME_CA.md](../STOP_TIME_CA.md) § cellordning — prefixformat redan beskrivet som mål.

---

## T2. Thun's-expressen — koppling till rätt tåg

- **Nuvarande:** Smal vertikal rand mellan kolumner med roterad text (`highlight_label` / `specialName` i `overviewGrid.ts`).
- **Önskat:** Etikett **under tågtypen** i kolumnhuvudet (t.ex. under ”Rälsbuss” / tur 93), som tryckt tidtabell.
- **Kod:** `MrtOverviewRailGroupGridHead.vue`, `MrtOverviewRailGroupGrid.vue`
- **Data:** `highlight_label` i `services.csv` / admin (redan per tur).

---

## T3. Färgsättning lik tryckt

- **Nuvarande:** Stationskolumn vit; från/till-rader ljusblå; tågbyte gult; buss blått (`MrtTimetableOverviewShell.vue`).
- **Problem:** Blå/gult kan förväxlas med produktnamn ”Grön/Gul tidtabell”.
- **Önskat:** Stationskolumn i **blekt** tidtabellsfärg (`--mrt-ov-header-bg` tonad); neutralare rutnät.
- **Kod:** `MrtTimetableOverviewShell.vue`, `MrtOverviewRailGroupGridRow.vue`

---

## T4. Busstider i samma tabell

- **Delvis idag:** Anslutningsbuss-rader vid knutpunkter (t.ex. Selknä), se `overview-bus-junction.php`, E2E `overview-mount.spec.ts`.
- **Önskat:** Tydligare vilka tåg som har bussanslutning; ev. admin-inställning (Fjällnora-buss → Faringe-tåg, Selknä-buss → Uppsala-tåg) om automatisk koppling inte räcker.
- **Beslut:** Produkt + datamodell innan implementation.

---

## T5. Ca vid Bärby, tur 93 — data eller UX?

**Fråga:** Tur 93 har fast uppehåll i Bärby men visar `Ca 11.28` i Turvy.

**Fixture** (`testdata/fixtures/lennakatten/stoptimes.csv`):

```csv
green-93-out,5,barby,11:28,11:28,scheduled,scheduled,scheduled,scheduled,1,0
```

| Fält | Värde | Effekt |
|------|-------|--------|
| `approximate_time` | **1** | Turvy visar Ca (enligt `MRT_format_stop_time_display`) |
| `in_service_timetable` | **0** | Stopp markeras som utanför tidtabell B → sync-regel ger Ca |
| Modes | `scheduled` | Fast uppehåll i anslagsdata |

**Slutsats:** Ca i Turvy **följer flaggorna i datan** — ingen presentationsbugg. Om tryckt grön tidtabell visar `11.28` **utan** Ca ska `approximate_time` sättas till `0` och `in_service_timetable` till `1` vid import/sync (data/fixture), inte CSS.

**Reseplaneraren** använder strängare regel (Ca bara vid behov + `approximate_time`) — därför kan samma stopp se olika ut i wizard vs Turvy tills D23 harmoniseras.

---

## Prioriterad ordning (förslag)

1. T1 — P/Ca-prefix läsbart (liten Vue-ändring, hög synlighet)
2. T2 — Thun's i kolumnhuvud
3. T3 — färgtokens
4. T5 — verifiera mot tryckt PDF; ev. fixture-rättning
5. T4 — busskoppling (större scope)

---

## Relaterat

- [STOP_TIME_CA.md](../STOP_TIME_CA.md) — Ca-regler och cellordning
- [2026-06-12-jesper-reseplanerare.md](2026-06-12-jesper-reseplanerare.md) — D23 Ca-semantik wizard vs Turvy
- Commits 2026-06-30: reseplanerare layout + tidslinje (separata från denna lista)
