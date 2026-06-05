# Feedback – reseplanerare beta (juni 2026)

Ny omgång feedback efter tur/retur-kalender och övriga förbättringar. **Gå igenom en punkt i taget** — bocka av status när punkt är besvarad, fixad eller avvisad.

**Källor:** mail/skärmdumpar från betatest  
**Relaterat:** [2026-06-01-granskning.md](2026-06-01-granskning.md)

---

## Sammanfattning

| Kategori | Antal | Kommentar |
|----------|-------|-----------|
| Buggar / fel | 5 | Zoner, busskoppling Selknä, ev. byte Marielund |
| UI / copy | 6 | Klippning, bussnamn, byte-etikett, passerade stationer, restid |
| Design / profil | 2 | Typsnitt, kalenderfärger |
| PDF / export | 1 | Mobil utskrift vs riktig PDF |
| Data / tidtabell | 1 | Få avgångar till Fjällnora |
| Framtida scope | 1 | Gångvägar + karta |
| Redan åtgärdat | 1 | Tur/retur-kalender (grå dagar) |
| Frågor | 1 | Hur matas data in? |

---

## R1. Tur och retur – grå dagar utan återresa

- **Originaltext:** Om man söker tur och retur borde dagar utan möjlig återresa vara gråa (t.ex. Uppsala Ö–Fyrislund där bara 18:07 går ut men ingen retur finns).
- **Område:** Reseplanerare / kalender
- **Typ:** bugg / UX
- **Prioritet:** medium
- **Status:** åtgärdad
- **Svar:** Kalender-API tar `trip_type`; dagar markeras `traffic_no_match` om ingen utresa har giltig retur samma dag. Tester: `JourneyMultiLegTest`, `wizardCalendarLoad.test.ts`.

---

## R2. ”Direktresa” trots byte (t.ex. 71 → 61 i Marielund)

- **Originaltext:** Reseplaneraren visar alla resor som direktresor. Ångtåg 71 står som ”ångtåg 71 mot Faringe” när man i själva verket får byta till 61 i Marielund.
- **Område:** Reseplanerare / sökning / copy
- **Typ:** bugg eller missförstånd — **utred**
- **Prioritet:** hög
- **Status:** klar (2026-06-05)
- **Anteckning:** Fixture delad vid Marielund: t.ex. `green-71-out` (→ Marielund) + `green-61-out` (Marielund → Faringe). Resesökningsmotorn hittar riktigt byte; PDF-byteskarta kvar för tidtabellsöversikt.

---

## R3. Sidor avklippta på mobil

- **Originaltext:** Sidorna höger/vänster blir lite avklippta (t.ex. ”Uppsala Östra → …” klippt till vänster).
- **Område:** Reseplanerare / mobil CSS
- **Typ:** UI
- **Prioritet:** medium
- **Status:** klar (2026-06-05)
- **Anteckning:** Orsak: full-bleed `100vw` + `overflow-x: hidden` gav horisontell förskjutning och klippte vänsterkant. Fix: full-bleed endast ≥48rem, bättre radbrytning i steg-header och rutt-rad, mer sidpadding på mobil.

---

## R4. ”Visa passerade stationer” – placering

- **Originaltext:** Flytta länken till mellan avgångs- och ankomststation (mer intuitivt än längst ned).
- **Område:** Reseplanerare / tidslinje
- **Typ:** UX
- **Prioritet:** låg–medium
- **Status:** öppen
- **Kod:** `frontend/vue/src/components/ui/MrtTimeline.vue`, `WizardTimeline.vue`

---

## R5. Bussnamn – ”Veteranbuss mot …”

- **Originaltext:** Bussar har inget nummer; hellre ”Veteranbuss mot Fjällnora” än ”Buss B3 mot Fjällnora”.
- **Område:** Reseplanerare / copy
- **Typ:** UX / copy
- **Prioritet:** låg (enkel kodändring)
- **Status:** klar
- **Svar:** `legVehicleLabel()` visar ”Veteranbuss mot …” för buss-ben (slug/typ `buss`), utan linjenummer. Översättningsnyckel `veteranBus`.

---

## R6. Fel buss i Selknä – 113 min väntan

- **Originaltext:** Reseplaneraren föreslår 113 min väntan på buss i Selknä trots anslutning efter ~3 min.
- **Område:** Reseplanerare / sökning / tidtabell
- **Typ:** bugg — **utred**
- **Prioritet:** hög
- **Status:** klar (2026-06-05)
- **Anteckning:** Orsak: global `min_transfer_minutes` > 3 avvisade B2 (11:50) med exakt 3 min byte tåg→buss. Fix: 0 min min-byte vid busshub (`mrt_station_bus_suffix`) för tåg→buss. Tester med min=4.

---

## R7. Minsta bytestid – 0 min?

- **Originaltext:** Borde min bytestid vara 0 min?
- **Område:** Inställningar / sökning
- **Typ:** produktbeslut
- **Prioritet:** låg
- **Status:** öppen
- **Anteckning:** Standard **3 min** (`min_transfer_minutes` i admin `#/settings`). Kan sättas till 0 utan kodändring. Påverkar inte 113-min-problemet (R6).

---

## R8. Restid vs klockslag (44 min vs 11:10–13:47)

- **Originaltext:** Restiden visar 44 min (körsträckor) medan klockslagen 11:10–13:47 inkluderar väntetid.
- **Område:** Reseplanerare / visning
- **Typ:** UX / ev. bugg (förvirrande)
- **Prioritet:** medium
- **Status:** klar
- **Svar:** Resekortet visar nu **dörr-till-dörr** (första avgång → sista ankomst), samma som klockintervallet. Per ben i detaljvyn behåller körsträcka. API: `MRT_normalize_total_duration_from_legs()` räknar elapsed, inte summa leg.

---

## R9. Byte-etikett – ”1 byte”, ”2 byten”

- **Originaltext:** Där det står ”Byte”, visa ”1 byte”, ”2 byten” osv.
- **Område:** Reseplanerare / copy
- **Typ:** UX
- **Prioritet:** låg
- **Status:** klar
- **Svar:** Expanderingsknappen på resekort visar `1 byte` / `N byten` utifrån antal ben minus ett (`formatTransferTripLabel`).

---

## R10. Få avgångar till Fjällnora (bara tre)

- **Originaltext:** Ser bara tre första avgångarna — inläsningsfel från tidtabellen?
- **Område:** Tidtabell / sökning
- **Typ:** utred data
- **Prioritet:** medium
- **Status:** öppen
- **Anteckning:** Wizard har **ingen hård gräns** på antal träffar. Tre avgångar = troligen bara tre giltiga kopplingar i datat för det datumet.

---

## R11. Gångvägar (framtida)

- **Originaltext:** Gång Selknä–Fjällnora när buss saknas; ev. Thun's, Länna bruk. Kräver kartfunktion — stort extrajobb.
- **Område:** Reseplanerare / ny resetypsmodell
- **Typ:** framtida scope
- **Prioritet:** låg (inte nu)
- **Status:** parkerad

---

## R12. Typsnitt enligt grafisk profil

- **Originaltext:** Typsnitten ska följa profilen. Färgerna i reseplaneraren upplevs som OK.
- **Område:** Global typografi
- **Typ:** design
- **Prioritet:** medium
- **Status:** delvis (G2: Roboto + Open Sans Bold finns — verifiera att allt använder tokens)
- **Referens:** https://lennakatten.se/grafisk-profil/

---

## R13. Kalenderfärger ≠ profilfärger

- **Originaltext:** I kalendern stämmer gult/grönt inte med profilfärgerna (reseplaneraren OK).
- **Område:** Kalender (månad + wizard datumsteg)
- **Typ:** design
- **Prioritet:** medium
- **Status:** öppen
- **Anteckning:** Trafikfärger (`--mrt-color-traffic-*`) vs varumärkesfärger (`--mrt-color-brand-*`). G7 införde typ-färger — kan behöva justeras mot profil.

---

## R14. PDF-export på mobil

- **Originaltext:** Utskriftslayout på mobil i stället för PDF i ny flik; Dela ger ful textfil.
- **Område:** Sammanfattningssteg / export
- **Typ:** önskemål / begränsning i nuvarande lösning
- **Prioritet:** medium
- **Status:** öppen
- **Anteckning:** Idag `window.print()` + Web Share/kopiera text (G10). Riktig PDF i ny flik kräver ny funktion (jsPDF / server-PDF). Se alternativ i [2026-06-01-granskning.md](2026-06-01-granskning.md) § G10.

---

## R15. Fel zon/pris (Uppsala–Fjällnora)

- **Originaltext:** Visar 1 zon när det ska vara 2. Förslag: matris med alla reserelationer → antal zoner.
- **Område:** Prisberäkning
- **Typ:** bugg
- **Prioritet:** hög
- **Status:** åtgärdad
- **Svar:** `MRT_zones_pair_span()` räknade intilliggande zoner (1→2) som 1 biljettzon; ska vara 2 (110 kr vuxen enkel). Gränsstationer (Gunsta) kan fortfarande ge 1 zon via optimal zon-tilldelning längs rutten. Tester: `PriceRulesTest`, `PriceZonesJourneyTest`.

---

## R16. Barn 0–6 → 0–3 år (etikett)

- **Originaltext:** Prislistan säger fortfarande 0–6 år; ska vara 0–3.
- **Område:** Priser / copy
- **Typ:** bugg (fel label)
- **Prioritet:** låg (en rad)
- **Status:** klar
- **Svar:** Etikett i `MRT_price_category_labels()` ändrad till ”Barn 0–3” (nyckeln `child_0_3` oförändrad).

---

## R17. Hur matas data in? (fråga)

- **Originaltext:** Gör man PDF som AI läser eller via interface?
- **Område:** Admin / onboarding
- **Typ:** fråga
- **Prioritet:** info
- **Status:** besvarad (ingen åtgärd)
- **Svar:** **Admin-gränssnitt** (Vue SPA) + valfritt **CSV-import/export**. Ingen PDF/AI-läsning. Se `docs/ADMIN_WORKFLOW.md`, `docs/CSV_FORMAT.md`.

---

## Prioriterad genomgångslista

Gå igenom i denna ordning (högsta affärs-/användarvärde först):

| # | ID | Punkt | Insats |
|---|-----|-------|--------|
| 1 | R15 | Zon/pris Uppsala–Fjällnora | ~~Medium~~ ✓ |
| 2 | R6 | Busskoppling Selknä (113 min) | Klar |
| 3 | R2 | Direktresa vs byte Marielund | Klar (fixture) |
| 4 | R8 | Restid inkl. väntetid / tydligare | Klar |
| 5 | R16 | Barn 0–3 etikett | Klar |
| 6 | R5 | Veteranbuss-namn | Klar |
| 7 | R9 | ”1 byte” / ”2 byten” | Klar |
| 8 | R4 | Placering ”Visa passerade stationer” | Liten UI |
| 9 | R3 | Mobil klippning | Klar (CSS) |
| 10 | R10 | Få avgångar Fjällnora | Datakontroll |
| 11 | R7 | Min bytestid 0 min? | Beslut + ev. default |
| 12 | R13 | Kalenderfärger vs profil | Design |
| 13 | R12 | Typsnitt (kvarvarande) | Design |
| 14 | R14 | Riktig PDF på mobil | Större |
| 15 | R11 | Gångvägar + karta | Framtida |

**Klart:** R1 (tur/retur-kalender), R2, R3, R5, R6, R8, R9, R15, R16, R17 (fråga besvarad).

---

## Nästa steg

- [ ] Nästa punkt: **R4** (placering passerade stationer) eller **R10** (Fjällnora-avgångar)
- [ ] Uppdatera **Status** och **Svar** under respektive punkt efter varje genomgång
- [ ] Länka commit/PR i **Svar** när punkt är åtgärdad

---

## Bilder (denna omgång)

| Beskrivning | Kopplad punkt |
|-------------|---------------|
| Direktresa, ångtåg 71, mobil klippning | R2, R3 |
| Byte Selknä, buss B3, 113 min, 44 min | R5, R6, R8, R9 |
| Dela/utskrift textfil | R14 |

> Lägg ev. skärmdumpar i `docs/feedback/images/` med datum i filnamn.
