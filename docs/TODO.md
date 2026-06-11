# TODO – teknisk skuld och förbättringar

Kort lista över **öppna** punkter där **produkt/beslut redan är spikat** — implementation eller verifiering återstår. Genomfört arbete finns i arkivet längst ner och i respektive plan/doc.

Punkter **utan** beslut listas separat. **Mycket senare** — parkerade tills kärnflöden är verifierade och produktbeslut finns.

---

## Trafikstörningar — UL-lik feed (webb, J11)

**Beslut:** 2026-06-11 — [TRAFFIC_DISRUPTIONS_PLAN.md](TRAFFIC_DISRUPTIONS_PLAN.md) §5–7  
**Modell:** **Två källor → en feed** — (A) auto från tur-avvikelser + (B) manuella trafikmeddelanden. **Webb only**, ingen wizard.

- [x] **Fas 1:** Domän/API — disruption feed, 90 dagars horisont, gruppering tågnummer
- [x] **Fas 2:** Publik Vue — UL-lik feed (ersätter flat `MrtTrafficNoticesView`)
- [ ] **Fas 3:** Shortcode `horizon_days` docs, ev. dedikerad sida
- [ ] **Fas 4:** Admin — förhandsvisning av samma feed
- [ ] Jesper: snabb OK på målbild och 90 dagar

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
- [ ] **Manuell check RÖD:** Marielund — Linnés (B9–B11); **ingen** Linnés vid Selkné; **inte** på gröna bussdagar

---

## Linjer och grenar — refactor (main + 2+1)

**Beslut:** 2026-06-11 — [LINES_REFACTOR_PLAN.md](LINES_REFACTOR_PLAN.md)  
**Modell:** `main` + transfer-grenar `fjallnora` @ Selkné och `linnes-marielund` @ Marielund + direkt `linnes-uppsala` (inga byten). **Håll Fjällnora och Linnés separata.**

- [x] Interim: `branch_code` på routes + pairing mot main (`1bba45c`, `9dffdc6`)
- [x] B5 borttagen — Linnés endast via Marielund ([LINES_REFACTOR_PLAN.md](LINES_REFACTOR_PLAN.md) §1)
- [x] Fas 1: `lines.csv` + Lennakatten-pilot
- [x] Fas 2: transfer-grenar (`fjallnora`, `linnes-marielund`, `branch_junctions.csv`)
- [ ] Fas 3: B14 som `linnes-uppsala` utan korridor-meta
- [ ] Fas 4: deprecated `route`, D8 en linje per sträcka

---

## Reseplanerare — smoke efter buggfixar

**Beslut:** B1–B3 klara i kod; acceptanskriterier definierade i buggplanen  
**Källa:** [feedback/2026-06-09-jesper-buggar-plan.md](feedback/2026-06-09-jesper-buggar-plan.md)

- [ ] **Manuell smoke:** Uppsala Östra → Fjällnora (3 ben) — byte Marielund ≈ 10 min, byte Selkné ≈ 3 min (J5/B1)
- [ ] **Manuell smoke:** Uppsala Östra → Linnés Hammarby — efter Jesper fyllt i komplett rutt/turer/stopptider (J6, D17 C)

---

## Linnés Hammarby — operatörsdata + verifiering

**Beslut:** D17 **C** (2026-06-09) — fixture B9–B14 (B5 borttagen 2026-06-11), [LINNES_HAMMARBY.md](LINNES_HAMMARBY.md), import via `#/import-export`  
**Status:** Verktyg klart; Jesper matar in data

- [ ] Verifiera reseplanerare + Turvy när rutt/turer/stopptider för Linnés Hammarby är kompletta i admin

---

## Admin — utökad datakvalitet (dashboard)

**Beslut:** 2026-06-11 — utöka befintliga dashboard-varningar (`dashboard-warnings.php`)  
**Syfte:** Fånga halvfärdig/inkonsistent data innan wizard, Turvy och reseplanerare misslyckas tyst (t.ex. Selkné, Marielund, Linnés Hammarby)

**Nyckelfiler:** `inc/domain/admin/dashboard-warnings.php`, `DashboardWarningsTest.php`, `DashboardPage.vue`

- [ ] Varning: stopptider matchar inte rutten (för få/för många stationer per tur)
- [ ] Varning: `train_change_map` pekar på tågnummer/tågtyp som inte finns
- [ ] Varning: byteshub utan bussmarkering **och** utan tågbyte (risk: inga flerbenade resor)
- [ ] Varning: tidtabell utan kommande trafikdagar
- [ ] Varning: bussrutt utan motsvarande tågförbindelse vid knutpunkt (där flerben krävs)

---

## Reseplanerare — kalender → wizard (förifyllt datum)

**Beslut:** 2026-06-11 — länka publik månad-/dagsvy till wizard med datum (ev. senare `from`/`to`)  
**Syfte:** Resenär ska inte behöva välja datum igen efter klick i kalendern

**Nyckelfiler:** månadskalender-shortcode/Vue, `JourneyWizardApp` / wizard store, ev. `SHORTCODES.md`

- [ ] Länk «Planera resa» (eller liknande) från månadskalender/dagsvy med `date` till wizard
- [ ] Wizard läser query/hash och förifyller datum i steg 2
- [ ] Docs + manuell smoke: kalender → wizard med rätt datum

---

## Reseplanerare — tydligare «ingen resa hittades»

**Beslut:** 2026-06-11 — kontextmeddelanden per orsak i stället för generiskt tomt svar  
**Syfte:** Resenär (och support) ska förstå *varför* inget förslag visas

**Nyckelfiler:** journey REST/sök, wizard trip-steg, l10n

- [ ] API/skikt: skilj orsaker där möjligt (ingen trafik dag X, ingen koppling/byte, inga matchande turer)
- [ ] Wizard: visa tydlig copy per orsak (svenska)
- [ ] Manuell smoke: minst «ingen trafik» och «ingen koppling» (t.ex. ofärdig busslinje)

---

## Mycket senare (förutsättningar / produktbeslut saknas)

Plocka **inte** upp förrän kärnflöden är stabila, manuella checks i TODO ovan är klara, och ev. produktbeslut är tagna.

| ID | Punkt | Varför senare |
|----|-------|----------------|
| R11 | Gångvägar + karta (Selkné–Fjällnora m.fl.) | Ny resetypsmodell + kart-UI; Jesper: «stort extrajobb» — [feedback juni 2026](feedback/2026-06-05-reseplanerare-beta.md#r11-gångvägar-framtida) |
| A0 / D8 | Onboarding-friktion (en vs två rutter per linje) | Kräver D8-domänbeslut (en rutt vs returrutt-knapp vs CSV-first); A1/A7/A10/Turvy redan levererat — [diskussioner D8](feedback/2026-06-09-jesper-diskussioner.md#d8-rutt-en-eller-två-rutter-per-linje-) |
| A9 / D11 | Publicera-knapp / utkast tidtabell | Kräver D11 (`draft` vs meta vs staging) **och** stabil admin/onboarding innan utkast/publicera kan designas säkert — [diskussioner D11](feedback/2026-06-09-jesper-diskussioner.md#d11-publicera-knapp--utkast-a9-) |
| D2b v2 | Feedback — e-postnotis vid ny rapport | `wp_mail` + konfigurerbar adress; låg prioritet när admin-lista räcker — [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md) |

---

## Saknar produktbeslut (ej i backlog tills beslut)

*(Tomt — J11/D16 har beslutad riktning i [TRAFFIC_DISRUPTIONS_PLAN.md](TRAFFIC_DISRUPTIONS_PLAN.md).)*

---

## Genomfört (arkiv)

| Område | Klar | Referens |
|--------|------|----------|
| Vue — gemensamma datetime/tid-utils | 2026-06-09 | [VUE_UTILS.md](VUE_UTILS.md), `frontend/vue/src/utils/datetime.ts` |
| PHP — utils-snabbguide i dokumentation | 2026-06-10 | [STYLE_GUIDE.md](STYLE_GUIDE.md) |
| Reseplanerare — feedback-widget v1 | 2026-06-10 | [WIZARD_FEEDBACK_SKETCH.md](WIZARD_FEEDBACK_SKETCH.md), J13/D2b |
| Reseplanerare — feedback CSV-export | 2026-06-11 | `#/feedback`, `GET /feedback/export` |
| Tidtabellsöversikt — typografi (Turvy) | 2026-06-10 | `timetable-overview.css`, commit `6d91237` |
| Tidtabellsöversikt — kolumnsammanslagning (kärna) | 2026-06-10 | commit `9a44dda`, `OverviewColumnMergeTest` |
| Tidtabellsöversikt — bussrader per tågkolumn (Selkné) | 2026-06-10 | commit `8c8a30a`, `TimetableOverviewHelpersTest` |
| Jesper beta — reseplanerare + admin (J1–J10, A1–A8, A10) | 2026-06-10 | [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) |
| Turvy — highlight-kolumn (Thun's-expressen, J1) | 2026-06-01 | Smal vertikal etikett + fast kolumnbredd — [granskning J1](feedback/2026-06-01-granskning.md), `MrtOverviewRailGroupGrid.vue`, `timetable-overview.css` |
