# Åtgärdsplan — buggar (Jesper beta, juni 2026)

**Datum:** 2026-06-09  
**Källa:** [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md)  
**Syfte:** Konkret plan för **bekräftade buggar** och **misstänkta logikfel** — inte UX-förbättringar, copy eller produktförslag.

**Relaterat:** [2026-06-09-jesper-diskussioner.md](2026-06-09-jesper-diskussioner.md), [ADMIN_UX_ACTION_PLAN.md](../ADMIN_UX_ACTION_PLAN.md)

---

## Sammanfattning

| ID | Bug | Allvarlighet | Insats | PR |
|----|-----|--------------|--------|-----|
| B1 | Fel bytestid vid andra+ byte (J5) | Hög | Liten | 1 |
| B2 | Buss/tåg-koppling godtycklig (A10) | Medium–hög | Utred → medel | 2 |
| B3 | Tidslinje — vertikal linje off-center (J3) | Låg | Liten | 3 (valfritt) |

**Ej buggar** (hanteras separat): J6 (saknad data i admin), J7 (copy), J1/J2/J8/J9/J10 (UX/design), A0–A9 (produktförslag).

---

## Status

| Fas | Beskrivning | Status |
|-----|-------------|--------|
| 0 | Plan (detta dokument) | **Klar** |
| 1 | B1 — bytestid flerben | **Klar** |
| 2 | B2 — utred bussanslutningar | **Klar** (flera grenar per huvudlinje) |
| 3 | B3 — tidslinje CSS | **Klar** |

---

## B1. Fel bytestid vid andra bytet (J5)

### Problem

Resa med **tre ben** (t.ex. 71 → 61 → buss): första bytet vid Marielund visar korrekt väntetid (10 min), men **andra bytet** vid Selknä visar samma 10 min trots att klockslagen är 10:50 → 10:53 (3 min).

### Rotorsak

1. **API** (`MRT_normalize_multi_leg_for_api`) sätter `transfer_wait_minutes` enbart för leg 0→1.
2. **Frontend** (`transferWaitMinutes` i `connectionLegDisplay.ts`) returnerar connection-nivåns värde för **alla** byten när fältet finns — ignorerar `legBefore` / `legAfter`.

### Rekommenderad fix

**Primär (frontend):** Beräkna alltid per leg-par:

```typescript
// connectionLegDisplay.ts — transferWaitMinutes()
return waitMinutesBetween(legBefore.to_arrival || '', legAfter.from_departure || '');
```

Ta bort (eller begränsa) fallback till `connection.transfer_wait_minutes`. Fältet på connection-nivå är missvisande för 3+ ben; kan behållas för bakåtkompatibilitet men ska **inte** användas i `buildTransferLabel`.

**Sekundär (API, valfritt i samma PR):**

- Antingen: ta bort `transfer_wait_minutes` från multi-leg-svar (breaking om någon klient förlitar sig på det).
- Eller: byt till `transfer_waits: number[]` — en per byte.
- Minsta ändring: dokumentera att fältet bara gäller första bytet; frontend fix räcker för reseplaneraren.

### Filer

| Fil | Ändring |
|-----|---------|
| `frontend/vue/src/shared/connectionLegDisplay.ts` | Beräkna väntetid per leg-par |
| `frontend/vue/tests/connectionLegSummary.test.ts` | Nytt test: 3 ben, olika `transfer_wait_minutes` vs leg-tider |
| `docs/mockups/DESIGN_TOKENS.md` | Uppdatera om API-fältet depreceras |

### Tester

**Vitest** — nytt fall i `connectionLegSummary.test.ts`:

```typescript
// 3 legs: transfer_wait_minutes=10 (leg0→1), men leg1→2 ska vara 3 min
legs: [
  { to_arrival: '10:45', to_station_id: 2, ... },
  { to_arrival: '10:50', from_departure: '10:45', from_station_id: 2, to_station_id: 3, ... },
  { from_departure: '10:53', from_station_id: 3, ... },
]
transfer_wait_minutes: 10
// buildTransferLabel(leg1, leg2, ...) → "Byte vid Selknä · 3 min"
```

**Manuell acceptans:**

1. Sök Uppsala Östra → Fjällnora, grön vardag, expandera 10:00-resan.
2. Byte Marielund ≈ 10 min; byte Selknä ≈ 3 min.

### Acceptanskriterier

- [x] Varje byte-etikett i detaljvyn matchar skillnaden mellan ankomst och nästa avgång.
- [x] Sammanfattning (`buildConnectionLegSummary`) och detalj (`buildTransferLabel`) visar samma minuter per byte.
- [x] Befintliga 2-bens-tester gröna.
- [x] `.\scripts\vue-check.ps1` grön.

**Uppgift:** `BUG-1.1`

---

## B2. Buss/tåg-koppling visas godtyckligt (A10)

### Problem

I tidtabellsöversikten och/eller reseplaneraren matchas inte buss mot rätt tåg per riktning/gren (Uppsala ↔ Linnés Hammarby vs Fjällnora). Jesper upplever att anslutningarna blir **fel eller slumpmässiga**.

### Hypoteser (utred i ordning)

| # | Hypotes | Var att titta |
|---|---------|-------------|
| H1 | `train_change_map` på station är ofullständig/felkonfigurerad | `StationTrainChangeEditor.vue`, meta `mrt_station_train_change_map` |
| H2 | Automatisk matchning i grid (`grid-connections.php`, `grid-junction-match.php`) väljer fel buss när flera bussar avgår nära samma tid | `MRT_timetable_branch_junction_station_id`, junction-match |
| H3 | Riktning (inbound/outbound) tolkas fel → buss från ”fel gren” kopplas | `MRT_timetable_rail_grid_direction`, `MRT_station_is_inbound_grid_origin` |
| H4 | Saknad rutt/tur-data (Linnés Hammarby) ger fallback-kopplingar | Data — inte kodbugg |
| H5 | Resesökningsmotor hittar rätt koppling men **tidtabells-PDF/översikt** visar annat | Separera wizard vs overview |

### Utredningssteg

1. **Reproducera** med Jespers inmatade Linnés Hammarby-data + befintlig Fjällnora-fixture.
2. **Jämför** wizard-sökresultat vs tidtabellsöversikt vid Selknä/Uppsala för samma datum.
3. **Logga** vilken buss `grid-junction-match` väljer per tågkolumn — enhetstest med fixture.
4. **Beslut:** datafix (train_change_map) vs kodfix (matchningsregler) vs båda.

### Möjliga kodfixar (efter utredning)

| Riktning | Insats |
|----------|--------|
| Striktare matchning: buss destination måste matcha gren (Linnés Hammarby / Fjällnora) | Medel |
| Visa kopplingar enbart från `train_change_map` när den finns (override auto) | Liten |
| Admin: koppla buss till specifikt tågnummer **och** riktning i UI | Stor (A10 produkt) |

### Filer (troliga)

| Fil | Roll |
|-----|------|
| `inc/domain/timetable/view/grid/grid-connections.php` | Panel-generering |
| `inc/domain/timetable/view/grid/grid-junction-match.php` | Tid/plats-matchning |
| `inc/domain/journey/train-change.php` | Manuell karta |
| `tests/Unit/GridConnectionsTest.php` | Utöka med Linnés Hammarby-scenario |

### Acceptanskriterier (efter fix)

- [x] För given grön dag: buss Bx i översikten motsvarar samma buss som reseplaneraren erbjuder för respektive tåg (enhetstester + `paired_branches`).
- [x] Linnés Hammarby-gren och Fjällnora-gren visar **olika** busskopplingar där datat så anger.
- [x] PHPUnit-test dokumenterar förväntat par tåg↔buss (`TimetableOverviewHelpersTest`, `GridConnectionsTest`).
- [ ] Manuell check Selknä inbound GRÖN/RÖD — se [TODO.md](../TODO.md)

**Uppgift:** `BUG-2.1` (utred), `BUG-2.2` (fix)

---

## B3. Tidslinje — linje inte centrerad (J3)

### Problem

Vertikala linjen mellan stationscirklarna i reseplanerarens detaljvy är någon pixel off-center.

### Rotorsak

CSS: `--mrt-tl-node` styr grid-kolumn och linjens `left`, men `.mrt-timeline__node` har fast `width: 1rem` — vid större `--mrt-tl-node` hamnar linjen fel.

### Fix

Alternativ A (enklast): sätt nodbredd till `var(--mrt-tl-node)` och centrera border i noden.

Alternativ B: räkna om `::before`-position så den matchar faktisk nod (1rem).

### Filer

| Fil | Ändring |
|-----|---------|
| `assets/frontend/ui/trips.css` | `.mrt-timeline__node`, ev. `@container`-block |

### Acceptanskriterier

- [x] Linjen går genom mitten av alla cirkelnoder (mobil + desktop).
- [x] Ingen regression i `@container`-layout för smala detaljpaneler.
- [ ] Visuell koll i wizard debug-läge / Playwright-screenshot (valfritt).

**Uppgift:** `BUG-3.1`

---

## Leveransordning

```
PR 1: BUG-1.1  (B1 — bytestid)           ← gör först, hög impact, liten diff
PR 2: BUG-2.1  (B2 — utredning + fix)    ← kräver fixture/data från Jesper
PR 3: BUG-3.1  (B3 — CSS)                ← kan köras parallellt med PR 2
```

---

## Verifiering (gemensam checklista)

Efter alla PR:

- [x] `.\scripts\check.ps1` — PHP + PHPUnit
- [x] `.\scripts\vue-check.ps1` — Vitest + build
- [x] Manuell smoke: Uppsala Östra → Fjällnora (3 ben) — 2026-06-11
- [x] Manuell smoke: Uppsala Östra → Linnés Hammarby — 2026-06-11
- [x] Manuell smoke: Selknä-buss + Marielund-kolumner i Turvy — 2026-06-11
- [x] Utkast [svar till Jesper](2026-06-11-svar-till-jesper.md) — 2026-06-11
- [x] Uppdatera status i [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md) (J5, A10, J3, J12, Turvy-uppföljning) — 2026-06-11

---

## Vad som medvetet inte ingår

| Punkt | Varför |
|-------|--------|
| J6 Linnés Hammarby saknas | Operatörsdata — inte kodbugg |
| J4 Ca-tider | Feature (behovsuppehåll) |
| J7 ”bokningsbara” | Copy |
| J1–J2, J8–J10 | UX/design |
| A0–A9 | Admin-produktförslag / onboarding |
