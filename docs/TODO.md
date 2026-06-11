# TODO – teknisk skuld och förbättringar

Kort lista över **öppna** punkter där **produkt/beslut redan är spikat** — implementation eller verifiering återstår. Genomfört arbete finns i arkivet längst ner och i respektive plan/doc.

Punkter **utan** beslut (A0 onboarding, J11 UL-lik störningar m.fl.) listas separat. **Mycket senare** — parkerade tills förutsättningar och produktbeslut finns.

---

## Reseplanerare — feedback-widget (v2, CSV)

**Beslut:** D2b (2026-06-10) — [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md) § Beslut  
**Status:** v1 klar; CSV-export kvar  
**Källa:** J13 — [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md)

- [ ] Admin: export feedback till CSV

*(E-postnotis → se **Mycket senare**.)*

---

## Stopptider — schema v3 (drift efter fas 4)

**Beslut:** Fas 4 klar (2026-06-10) — [STOP_TIME_CA.md](STOP_TIME_CA.md), [STOP_TIME_SOURCES.md](STOP_TIME_SOURCES.md)  
**Status:** Kod klar; Docker-miljö ska synkas mot ny fixture

- [ ] Dev-reset + omimport efter fas 4: `.\scripts\docker-dev-reset.ps1 -SkipCompose`
- [ ] **Verifiering tur 71** (referens enligt [STOP_TIME_V3_IMPLEMENTATION.md](STOP_TIME_V3_IMPLEMENTATION.md)): Uppsala Ö `10.00`, Barby `10.23`, Skölsta `Ca 10.09 X`, Marielund `10.35`

---

## Tidtabellsöversikt — sammanslagna kolumner vid tågbyte

**Beslut:** PDF-lik vy — continuation-turer (64, 74 från Marielund) i **samma kolumn** som ankommande tåg via `train_change_map`; kärna levererad (`9a44dda`)  
**Källa:** [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) J12/A2, fixture Lennakatten `station_train_changes.csv`

**Nyckelfiler:** `overview-column-merge.php`, `overview-rail-columns.php`, `overview-rail-cells.php`, `overview-rail-rows.php`

- [ ] E2E/admin: redigering efter Marielund sparar rätt `serviceId`
- [ ] **Manuell check:** GRÖN tidtabell 2026-06-06 — Faringe→Uppsala 5 kolumner (70, 60, 62, 96, 78); tur 71 ut utan kolumn 61; Marielund ankomst/avgång i samma kolumn
- [ ] Kör dev-reset om lokal miljö visar gammalt (se ovan)

---

## Tidtabellsöversikt — buss vid knutpunkt (Selknä)

**Beslut:** D15 **A** (2026-06-10) — busstider i **rätt tågkolumn** per gren; kod klar (`8c8a30a`, `d62dd87`, `f1c35e9`)  
**Källa:** A10/Jesper buggplan B2 — [feedback/2026-06-09-jesper-buggar-plan.md](feedback/2026-06-09-jesper-buggar-plan.md)

- [ ] **Manuell check GRÖN:** Selknä inbound — tur 62 → B3/Fjällnora, tur 96 → B4/Fjällnora; inga delade rader med korsade tider
- [ ] **Manuell check RÖD:** Selkné inbound söndag — Linnés Hammarby (B5) visas; **inte** på gröna bussdagar

---

## Reseplanerare — smoke efter buggfixar

**Beslut:** B1–B3 klara i kod; acceptanskriterier definierade i buggplanen  
**Källa:** [feedback/2026-06-09-jesper-buggar-plan.md](feedback/2026-06-09-jesper-buggar-plan.md)

- [ ] **Manuell smoke:** Uppsala Östra → Fjällnora (3 ben) — byte Marielund ≈ 10 min, byte Selkné ≈ 3 min (J5/B1)
- [ ] **Manuell smoke:** Uppsala Östra → Linnés Hammarby — efter Jesper fyllt i komplett rutt/turer/stopptider (J6, D17 C)

---

## Linnés Hammarby — operatörsdata + verifiering

**Beslut:** D17 **C** (2026-06-09) — fixture B5/B9–B14, [LINNES_HAMMARBY.md](LINNES_HAMMARBY.md), import via `#/import-export`  
**Status:** Verktyg klart; Jesper matar in data

- [ ] Verifiera reseplanerare + Turvy när rutt/turer/stopptider för Linnés Hammarby är kompletta i admin

---

## Mycket senare (förutsättningar / produktbeslut saknas)

Plocka **inte** upp förrän kärnflöden är stabila, manuella checks i TODO ovan är klara, och ev. produktbeslut är tagna.

| ID | Punkt | Varför senare |
|----|-------|----------------|
| A9 / D11 | Publicera-knapp / utkast tidtabell | Kräver D11 (`draft` vs meta vs staging) **och** stabil admin/onboarding (A0/D8) innan utkast/publicera kan designas säkert — [diskussioner D11](feedback/2026-06-09-jesper-diskussioner.md#d11-publicera-knapp--utkast-a9-) |
| D2b v2 | Feedback — e-postnotis vid ny rapport | `wp_mail` + konfigurerbar adress; låg prioritet när admin-lista räcker — [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md) |

---

## Saknar produktbeslut (ej i backlog tills beslut)

| ID | Punkt | Var beslut saknas |
|----|-------|-------------------|
| A0 / D8 | Onboarding-friktion (rutt per riktning) | Domänmodell — [diskussioner D8](feedback/2026-06-09-jesper-diskussioner.md#d8-rutt-en-eller-två-rutter-per-linje-) |
| J11 / D16 | UL-lik störningslista i reseplaneraren | Parkerad — [diskussioner D16](feedback/2026-06-09-jesper-diskussioner.md#d16-ul-lik-störningslista-) |

---

## Genomfört (arkiv)

| Område | Klar | Referens |
|--------|------|----------|
| Vue — gemensamma datetime/tid-utils | 2026-06-09 | [VUE_UTILS.md](VUE_UTILS.md), `frontend/vue/src/utils/datetime.ts` |
| PHP — utils-snabbguide i dokumentation | 2026-06-10 | [STYLE_GUIDE.md](STYLE_GUIDE.md) |
| Reseplanerare — feedback-widget v1 | 2026-06-10 | [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md), J13/D2b |
| Tidtabellsöversikt — typografi (Turvy) | 2026-06-10 | `timetable-overview.css`, commit `6d91237` |
| Tidtabellsöversikt — kolumnsammanslagning (kärna) | 2026-06-10 | commit `9a44dda`, `OverviewColumnMergeTest` |
| Tidtabellsöversikt — bussrader per tågkolumn (Selkné) | 2026-06-10 | commit `8c8a30a`, `TimetableOverviewHelpersTest` |
| Jesper beta — reseplanerare + admin (J1–J10, A1–A8, A10) | 2026-06-10 | [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) |
| Turvy — highlight-kolumn (Thun's-expressen, J1) | 2026-06-01 | Smal vertikal etikett + fast kolumnbredd — [granskning J1](feedback/2026-06-01-granskning.md), `MrtOverviewRailGroupGrid.vue`, `timetable-overview.css` |
