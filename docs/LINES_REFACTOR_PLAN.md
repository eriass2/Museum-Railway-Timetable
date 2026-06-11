# Linjer och grenar — refactor-plan (Lennakatten)

**Status:** Spikad domänriktning (2026-06-11). Fas 1 (`lines.csv` pilot) implementerad; Fas 2–4 återstår.  
**Syfte:** Ersätta dagens många shuttle-`route` + overview-heuristik med en tydlig modell innan go-live.  
**Relaterat:** [DATA_MODEL.md](DATA_MODEL.md) §1.4b, [CSV_FORMAT.md](CSV_FORMAT.md), [LINNES_HAMMARBY.md](LINNES_HAMMARBY.md).

---

## 1. Operativ sanning (spikad)

### Huvudstråk

**Main** — tåg Faringe ↔ Uppsala Östra (14 stationer).

### Buss — två helt separata berättelser

**Rör aldrig ihop Fjällnora och Linnés i samma linje, tidtabell eller overview-logik.**

| Del | Sträcka | Byte med tåg? | Tidtabell (Lennakatten) |
|-----|---------|---------------|-------------------------|
| **Fjällnora** | Selkné ↔ Fjällnora | Ja, vid **Selkné** | `green-buss` |
| **Linnés ↔ Marielund** | Marielund ↔ Linnés Hammarby | Ja, vid **Marielund** | `red-buss` |
| **Linnés → Uppsala** | Linnés Hammarby → Uppsala Östra | **Nej** — direkt, inga byten | `red-buss` (B14) |

```text
MAIN (tåg)
  … ── Selkné ── … ── Marielund ── … ── Uppsala Östra

FJÄLLNORA (transfer @ Selkné)
  Selkné ◄──► Fjällnora

LINNÉS (transfer @ Marielund)
  Marielund ◄──► Linnés Hammarby
                      │
                      ├──► Marielund  (igen)
                      └──► Uppsala    (direkt, inga byten)
```

### Vad som är fel i dagens antaganden

| Fel | Rätt |
|-----|------|
| Linnés som “gren med Selkné och Marielund” | **Fjällnora = Selkné.** **Linnés = Marielund.** |
| B14 “följer main” med `\|` mellan hållplatser | B14 = **Linnés → Uppsala**, inga stopp på vägen, **inga byten** |
| `\|` / `overview_pass_from_station` som data | PDF-**layout** ovanpå direkt ben — inte att bussen kör main-linjen |
| En `linnes-hammarby`-`branch_code` för allt | **2+1** nedan |

### B5 borttagen (2026-06-11)

**B5 (Selkné–Linnés)** fanns i fixture men passar **inte** i domänmodellen (Linnés nås via **Marielund**). Tur, rutter `selkna-linnes-hammarby` / `linnes-hammarby-selkna` och tillhörande stoptider är borttagna. Resa Uppsala → Linnés går via tåg till Marielund + B9 (eller motsv.).

---

## 2. Målmodell: main + 2 transfer-grenar + 1 direktmönster

Inte **tre likadana grenar mot main**. Uppsala-benet är **inte** en transfer-gren.

| Entitet | Typ | `junction_on_main` | `requires_transfer` |
|---------|-----|--------------------|------------------------|
| `main` | linje (tåg) | — | — |
| `fjallnora` | transfer-gren | `selkna` | ja |
| `linnes-marielund` | transfer-gren | `marielund` | ja |
| `linnes-uppsala` | direktmönster | — (start `linnes-hammarby`) | **nej** |

**Mot main för byte:** **2** (Fjällnora @ Selkné, Linnés @ Marielund).  
**Direkt utan byte:** **1** (Linnés → Uppsala).

### Presentation vs data

| Lager | Innehåll |
|-------|----------|
| **Data / journey** | Tre bussentiteter separata; Uppsala = direkt kant Linnés→Uppsala |
| **Overview / PDF** | Main-rutnät; injicera Fjällnora vid Selkné, Linnés vid Marielund; B14 som egen Buss-kolumn eller rad — **layout**, inte stopptider på Gunsta/Barby |

---

## 3. Jämfört med dagens modell

| Idag | Mål |
|------|-----|
| 9 `route_code` (2 main + 2 fjällnora + 5 linnes-shuttles) | `main` + 2 transfer-grenar + 1 direktmönster |
| `branch_code` på route (interim, `1bba45c`) | `line` / `branch` som första klass |
| `overview_column`, `overview_pass_from_station` (B14) | `linnes-uppsala` + `requires_transfer=false` |
| `MRT_timetable_branch_main_pair_score` | `junction_on_main` + `branch_type` |
| Dubbel route per riktning (D8 öppen) | En linje, riktning härledd från stopptider |

---

## 4. Föreslagen CSV-skiss (v2)

```
lines.csv
  line_code, title, kind
  main, Faringe – Uppsala Östra, main
  fjallnora, Selkné – Fjällnora, branch
  linnes-marielund, Marielund – Linnés Hammarby, branch
  # linnes-uppsala kan vara line_kind=pattern under linnes eller egen line_code

line_stations.csv
  line_code, sequence, station_code

branch_junctions.csv
  line_code, junction_station_code, requires_transfer
  fjallnora, selkna, 1
  linnes-marielund, marielund, 1

services.csv
  service_code, timetable_code, line_code, service_number, end_station_code, ...
  # B14: line_code=linnes-uppsala (eller pattern), requires_transfer=0
```

`route.csv` / `mrt_route` **deprecated** — mappas vid import tills migrering klar.

---

## 5. Implementationsfaser

### Fas 0 — gjort

- `branch_code` på routes (`main`, `fjallnora`, `linnes-hammarby`)
- Pairing: buss-shuttles bara mot `main`-korridor
- Dokumentation §1.4b i DATA_MODEL

### Fas 1 — `line` i CSV (pilot Lennakatten) ✓

- Inför `lines.csv` + `line_stations.csv`
- `main` = befintlig 14-stationslista (en gång)
- Tåg-services: `line_code=main` (mapping från `route_code`)
- Import: `mrt_line_registry` + `mrt_service_line_code`; `route` kvar som alias
- Tester: fixture validate + journey oförändrat beteende

### Fas 2 — transfer-grenar

- `fjallnora` + `linnes-marielund` med `branch_junctions.csv`
- Flytta B1–B8, B9–B13 från shuttle-routes
- Overview: para ihop via `junction_on_main`, inte station-score
- **Selkné:** endast Fjällnora. **Marielund:** endast Linnés-shuttle.

### Fas 3 — Linnés → Uppsala direkt

- B14 som `linnes-uppsala`, `requires_transfer=false`
- Ta bort `linnes-hammarby-uppsala-ostra` route, `overview_pass_from_station`, korridor-`\|` som datakrav
- Behåll ev. PDF-layout i overview-lager med explicit `display_mode=standalone_column`

### Fas 4 — städning

- Ta bort överflödiga routes (B5/Selkné–Linnés redan borttagen)
- D8: en linje per sträcka, riktning härledd
- Uppdatera admin (Turvy väljer `line`, inte dubbel route)

---

## 6. Acceptance (Lennakatten)

| Check | Förväntat |
|-------|-----------|
| Grön dag, Selkné inbound | B3/B4 Fjällnora i rätt tågkolumn — **ingen** Linnés |
| Röd söndag, Marielund | B9–B13 Linnés i rätt kolumn — **ingen** Fjällnora |
| Röd söndag, inbound Buss-kolumn | B14: 16.20 Linnés, 16.45 Uppsala — **inga byten** i reseplanerare för enbart Linnés→Uppsala |
| Journey Uppsala → Fjällnora | Byte Selkné, **inte** via Linnés |
| Journey Marielund → Linnés | Buss-shuttle, byte vid Marielund |

---

## 7. Beslut att ta innan kod

1. ~~**B5**~~ — borttagen enligt §1.
2. **`route` bort helt** eller alias för `line` under övergång?
3. **PDF `\|` för B14** — kvar som ren layout utan korridor-meta?

---

## 8. Referenser

- D8 (en vs två rutter): [feedback/2026-06-09-jesper-diskussioner.md](feedback/2026-06-09-jesper-diskussioner.md#d8-rutt-en-eller-två-rutter-per-linje-)
- D15 (buss/tåg-koppling): samma fil, §D15 — delvis löst med heuristik; denna plan ersätter gissning
- Interim `branch_code`: commits `1bba45c`, `9dffdc6`
