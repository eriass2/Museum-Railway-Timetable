# Svar till Jesper — status juni 2026

Utkast till återkoppling efter betatest och manuella verifieringar (2026-06-11).  
**Relaterat:** [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md), [2026-06-11-jesper-reseplanerare.md](2026-06-11-jesper-reseplanerare.md), [2026-06-09-jesper-buggar-plan.md](2026-06-09-jesper-buggar-plan.md)

---

## Kort sammanfattning

Stort tack för all feedback hittills. Sedan din senaste omgång (10 juni) har vi:

1. **Verifierat** Turvy, reseplaneraren och admin mot PDF och fixture — inklusive Selkné-buss, Marielund-kolumner och Linnés Hammarby.
2. **Levererat** trafikstörningar v2 (UL-lik feed, 90 dagars horisont) — redo för din snabba OK på målbilden.
3. **Utrett** seg reseplanerare (J14) och lagt in tillfällig förbättring; större cache-refaktor pågår.
4. **Planerat** dina nya önskemål kring biljettcopy (J15–J18) — väntar korta produktbeslut innan implementation.

---

## Klart sedan förra omgången

### Turvy och tidtabellsöversikt (J12, A2, A10)

- **Kolumnsammanslagning vid tågbyte** — t.ex. tur 64/74 från Marielund visas i samma kolumn som ankommande tåg (PDF-lik vy). Verifierat Faringe→Uppsala 2026-06-06: fem kolumner, Marielund ankomst/avgång i samma kolumn.
- **Buss vid Selkné** — busstider i rätt tågkolumn per gren (Uppsala↔Fjällnora vs Linnés Hammarby). Stämmer mot `Anslagstidtabell-2026.pdf` på GRÖN dag; RÖD dag (2026-07-05) kontrollerad för radordning Marielund.
- **Admin Turvy** — redigering efter Marielund sparar rätt tur; fliken **Linjer** (A1–A5) verifierad.

### Reseplanerare — buggar och smoke (J5, B1–B3)

- **Bytestid Selkné** — 3 min (inte 10) i detaljvyn.
- **Smoke-tester** — Uppsala Östra → Fjällnora (3 ben, ~10 min byte Marielund, ~3 min Selkné), Linnés → Uppsala direkt (B14), Uppsala Östra → Linnés Hammarby.

### Linnés Hammarby (J6, D17)

- Fixture och importguide på plats. Reseplanerare och Turvy verifierade med dina inmatade data (B9–B14).

### Admin — datakvalitet

- Dashboard varnar nu bl.a. om stopptider som inte matchar rutten, ogiltiga tågbyten, byteshub utan buss/tågbyte, tidtabell utan kommande trafikdagar, och bussrutter utan tågförbindelse vid knutpunkt.

### Trafikstörningar (J11)

- **Ny UL-lik feed** på webben: auto från tur-avvikelser + manuella trafikmeddelanden, 90 dagars horisont, gruppering per tågnummer. Admin har förhandsvisning av samma feed.
- **Behöver din OK:** Stämmer layout och rubriker med det du tänkte dig? Räcker 90 dagar för «kommande»? (Se [TRAFFIC_DISRUPTIONS_PLAN.md](../TRAFFIC_DISRUPTIONS_PLAN.md) §5.2.)

### Linjer och grenar

- Ny modell: `main` + transfer-grenar (`fjallnora` @ Selkné, `linnes-marielund` @ Marielund) + direkt `linnes-uppsala`. Admin **Linjer**-flik ersätter manuell rutt-onboarding för Lennakatten.

---

## Pågående / planerat

### J14 — Seg reseplanerare (10–15 s)

**Vad vi hittat:** Den största fördröjningen sitter i **kalenderladdning** (`POST journey/calendar`), särskilt tur/retur på trafiktunga månader (t.ex. juli: ~8 s kallt). Enkel resa och tur/retur har **separata cache-nycklar** — därför kändes en resetyp snabb medan den andra var seg.

**Tillfällig fix (2026-06-11):** Efter att en resetyp laddats hämtas den andra i bakgrunden (prefetch). Byten mellan enkel resa och tur/retur ska kännas snabbare efter första besöket.

**Kvar:** Första besök per ny rutt/månad/resetyp kan fortfarande ta några sekunder. Vi jobbar på holistisk cache-refaktor + snabbare tur/retur-beräkning per dag ([WIZARD_CACHE_REFACTOR.md](../WIZARD_CACHE_REFACTOR.md)).

**Fråga till dig:** Stämmer det att det känns långsamt främst vid **byte av månad** eller **val av tur/retur vs enkel resa** — eller även vid själva resesökningen (steg 3–4)?

### J15–J18 — Biljettinfo, zoncopy, CTA, dela-knapp

Dina förslag är dokumenterade punkt för punkt i [2026-06-11-jesper-reseplanerare.md](2026-06-11-jesper-reseplanerare.md). Innan vi bygger J15 (admin-redigerbar biljettcopy) behöver vi korta svar:

| # | Fråga | Alternativ |
|---|-------|------------|
| D19 | Var ska biljettcopy redigeras? | Inställningar / Priser / per station |
| D20 | Ska pensionär 65+ finnas kvar i zonförklaringen? | Separat rad / ta bort / flytta |
| D21 | Eftermiddagsnot — ersätta eller komplettera nuvarande text? | En kombinerad fotnot / flera rader |
| D22 | Dela-knapp v1 — scope? | Text + Web Share / även PDF / deep link senare |

**Snabba wins** vi kan göra direkt när du säger ja: J17 (CTA «Mer information om biljettköp») och J16 (enklare zonförklaring).

---

## Öppet längre fram (medvetet parkerat)

| Punkt | Kommentar |
|-------|-----------|
| **A9 / D11** — publicera/utkast tidtabell | Kräver produktbeslut D11 |
| **A0 / D8** — en vs två rutter per linje | Onboarding-friktion; linjerefaktor minskar redan nu |
| **R11** — gångvägar + karta | Stort extrajobb; efter kärnflöden |

---

## Önskad återkoppling från dig

1. **J11 trafikstörningar** — snabb OK på målbild + 90 dagar (eller justeringar).
2. **J14 prestanda** — bekräfta var det känns långsamt (kalender vs resesök).
3. **J15–J18** — svar på D19–D22 (gärna kort, punktvis).
4. **Ev. ny feedback** — Turvy/reseplanerare efter linjerefaktor och trafikstörningar v2.

---

## Bilagor / var du testar

- **Demo:** *(fyll i URL)*  
- **Admin:** `#/lines`, `#/timetable-overview`, trafikstörningar-shortcode  
- **Referens-PDF:** `Anslagstidtabell-2026.pdf`
