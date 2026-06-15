# Feedback – Jesper (reseplanerare, juni 2026, omgång 3)

Fortsatt betatest efter leverans av sammanfattningssteg, priser, PDF och prestandaoptimeringar. **Gå igenom en punkt i taget** — bocka av status när punkt är besvarad, fixad eller avvisad.

**Källor:** mail/skärmdumpar från Jesper (2026-06-10)  
**Senast uppdaterad:** 2026-06-11  
**Relaterat:** [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md), [2026-06-01-granskning.md](2026-06-01-granskning.md), [2026-06-11-svar-till-jesper.md](2026-06-11-svar-till-jesper.md), [WIZARD_PERFORMANCE_PLAN.md](../WIZARD_PERFORMANCE_PLAN.md), [WIZARD_FEEDBACK_SKETCH.md](../WIZARD_FEEDBACK_SKETCH.md)

---

## Sammanfattning

| Kategori | Antal | Prioritet | Status |
|----------|-------|-----------|--------|
| Prestanda | 1 | hög | delvis — prefetch klar; holistisk cache planerad |
| Biljettinfo / copy (sammanfattning) | 4 | medium–hög | öppen |
| Dela-resa (ny feature) | 1 | låg–medium | öppen — utred |

**Kort tolkning:** Jesper vill (1) att sökning ska kännas snabb igen, (2) rikare och redigerbar biljettinformation under priserna — särskilt var man köper biljett beroende på startstation, (3) enklare zonförklaring, (4) tydligare CTA-knapp som inte låter som onlineköp, och (5) ev. en dela-knapp via mobilens inbyggda delning.

---

## J14. Seg reseplanerare (10–15 s vid sökning)

- **Originaltext:** *Jag tycker allmänt reseplaneraren blivit seg sedan de senaste ändringarna. Ibland kan det ta 10–15 sekunder att få fram resultat när man söker en resa, medan det ibland går snabbare. Tidigare gick det alltid på bara någon sekund att få fram resultat.*
- **Skärmdump:** `Skärmavbild 2026-06-10 kl. 17.37.08.png` (söksteg)
- **Område:** Reseplanerare / prestanda (kalender, utresa/återresa-sök)
- **Typ:** regression / utred
- **Prioritet:** hög
- **Status:** **delvis åtgärdad** — prefetch single↔return (2026-06-11); holistisk refaktor planerad ([TODO.md](../TODO.md) J14)

### Bekräftad mätning (Erik, localhost Docker)

| Händelse | API | Payload | TTFB |
|----------|-----|---------|------|
| Byta månad (juli) | `POST journey/calendar` | Uppsala Ö (3339) → Fjällnora (3353), `trip_type: return`, 2026-07 | **8,06 s** (nätverk) |
| Samma (PHP CLI, kall transient) | `MRT_get_journey_calendar_month()` | samma | **7 648 ms** |
| Samma (varm transient) | — | samma | **~0 ms** |

**Route i UI:** Uppsala Östra → Fjällnora, tur och retur. Nästan hela tiden är **Waiting for server response** — inte nätverk eller nedladdning (svar ~1,5 KiB).

**Varför ibland snabbt:** server-side transient-cache (1 h) + klient-cache vid tillbaka utan ruttbyte. **Varför ibland långsamt:** ny rutt, nytt månad, **annan resetyp** (`single` vs `return` har **separata cache-nycklar**), eller cache ogiltig efter admin-sparande.

**Rotorsak (Erik, 2026-06-11):** Testat mest **tur/retur** → den cachen varm. **Enkel resa** = egen nyckel → kall build (~3–4 s juli) varje gång tills den laddats minst en gång. Upplevelsen ”tur/retur snabbt, enkel seg” var **inte** att enkel saknar PHP-cache — utan att bara en resetyp var uppvärmd.

### Åtgärd (2026-06-11)

- [x] **Prefetch** — efter kalenderladdning hämtas den **andra** resetypen i bakgrunden (`wizardCalendarLoad.ts` → `prefetchWizardCalendarMonth`). Värmer både Vue-cache och PHP-transient för `single` ↔ `return` samma månad/rutt. **Tillfällig lösning** — se holistisk refaktor: [WIZARD_CACHE_REFACTOR.md](../WIZARD_CACHE_REFACTOR.md).
- [x] **PHP-optimering** kall tur/retur-kalender — two-pointer på rå tider + outbound request-cache (2026-06-15)

### Lokala jämförelser (Docker, samma rutt)

| Scenario | Kall build |
|----------|------------|
| Tur/retur, juli 2026 | **7,6 s** → **~5,9 s** (two-pointer, 2026-06-15) |
| Enkel resa, juli 2026 | **3,8 s** |
| Tur/retur, juni 2026 | **0,6 s** |

Juli är tyngre (mer trafik → fler dagar kör full tur/retur-kontroll). Tur/retur-kalendern anropar `MRT_journey_calendar_has_round_trip()` per dag med trafik — full utressökning + retursökning per kandidat.

### Nuvarande läge i kod

Fas 1–3 i [WIZARD_PERFORMANCE_PLAN.md](../WIZARD_PERFORMANCE_PLAN.md) är **klara**. **Kall kalender** tur/retur juli är **förbättrad** (~22 %) men fortfarande över målet ~2 s — flaskhalsen är nu främst `MRT_find_multi_leg_connections` × 2 per trafikdag, inte parningen.

| Flaskhals | Var | Kommentar |
|-----------|-----|-----------|
| Kalendermånad tur/retur | `MRT_journey_calendar_has_round_trip()` | Största rotorsaken (bekräftad) |
| Kalendermånad enkel | `MRT_journey_engine_has_connection()` per dag | ~4 s juli (mindre men märkbart) |
| Resesök | `POST journey/search` | Ej mätt i denna omgång |
| Cache-invalidering | `MRT_bump_journey_calendar_cache_version()` vid admin-spar | Frekvent under betatest |

Diagnostikscript: `scripts/bench-calendar.php` (CLI via Docker).

### Föreslagen åtgärd

1. **Optimera tur/retur per dag** — progressiv ”finns minst en tur/retur?” utan att lista alla utresor.
2. **Per-dag transient** (valfritt) — cache `from|to|ymd|return` som bool, TTL 1 h.
3. **Värme-cache** — cron/prefetch för aktuell + nästa månad (backlog).
4. **Prod-logg** — kalenderbuild >2 s även utan dev-läge.

### Acceptanskriterium

Byta månad (tur/retur, Uppsala Ö → Fjällnora, juli) ska ta **&lt; ~2 s** kallt. Upprepad samma månad ska vara nästan omedelbar (cache träff).

### Beslut

- [x] Prefetch single ↔ return (klient + indirekt PHP-transient)
- [x] PHP-optimering kall kalender — **two-pointer på rå tider** i `MRT_journey_calendar_has_round_trip_fast()` (2026-06-15)
- [x] Utkast svar till Jesper om prestanda — [2026-06-11-svar-till-jesper.md](2026-06-11-svar-till-jesper.md)
- [ ] Jesper: bekräfta vilket steg som är långsamt (kalender vs resesök)

---

## J15. Utökad biljettinformation under priserna (admin-redigerbar)

- **Originaltext (sammanfattat):** Under biljettpriserna, där förklaringen för eftermiddagsbiljett visas, vill han bygga ut med mer information som också går att lägga till och ta bort i adminvyn. Han hittade inte var det redigeras idag.
- **Område:** Reseplanerare / sammanfattning (`WizardSummaryStep`, `MrtPriceTable`)
- **Typ:** feature + admin UX
- **Prioritet:** hög
- **Status:** öppen

### Önskad copy (Jespers formulering)

**Villkorlig text — var man köper biljett (baserat på utresans startstation):**

| Villkor | Text |
|---------|------|
| Resa börjar på Uppsala Ö | Din resa börjar på Uppsala Östra. Där köper du din biljett i biljettluckan på stationen före avgång (kort/kontant). |
| Resa börjar i Marielund | Din resa börjar i Marielund. Där köper du din biljett i Marielunds jernvägscafé (kort/kontant/swish) före avgång eller av konduktören ombord på tåget (kontant/swish OBS! ej kort). |
| Resa börjar i Almunge | Din resa börjar i Almunge. Där köper du din biljett i Almunge jernvägscafé (kort/kontant/swish) före avgång eller av konduktören ombord på tåget (kontant/swish OBS! ej kort). |
| Övrig startstation/hållplats | Du börjar din resa på en station eller hållplats som saknar biljettförsäljning. Köp din biljett av konduktören ombord på tåget (kontant/swish OBS! ej kort). |

**Statiska tillägg (alltid eller villkorligt):**

| Text | Villkor |
|------|---------|
| Student ska kunna uppvisa giltig studentlegitimation (Mecenat, WeStudents eller ISIC-kortet). | Alltid (under priser) |
| Heldagsbiljett gäller för obegränsat resande på alla Lennakattens tåg och bussar under en hel dag. | När heldagspris visas |
| Biljetterna gäller hela trafiksäsongen. | Alltid |
| Biljetterna gäller hela trafiksäsongen. Tänk dock på att eftermiddagsbiljett endast gäller vid resa efter kl 15:00. | Vid eftermiddagsbiljett (istället för eller utöver nuvarande kortare rad) |

### Nuvarande läge i kod

| Del | Var | Admin-redigerbar? |
|-----|-----|-------------------|
| Zonförklaring (`priceNote`) | `inc/assets/frontend.php` → `MrtPriceTable` | **Nej** — hårdkodad översättningssträng |
| Eftermiddagsnot (`priceAfternoonNote`) | `MRT_journey_wizard_l10n_price()` | **Delvis** — textmall i PHP; gräns från Priser → Eftermiddags-retur; admin har `pricesAfternoonPublicNote` i förhandsvisning men **ingen separat redigeringsyta** för publik copy |
| Stationsspecifik köpinfo | — | **Finns inte** |
| Flera fotnoter under prisblock | `MrtPriceTable` har **en** `<p class="mrt-price-block__note">` | Behöver utökas |

### Föreslagen implementation

**Produktbeslut först:**

1. **Var i admin?** Alternativ: A) Inställningar → Biljettinformation · B) Priser → ny sektion ”Publik biljettcopy” · C) Per station under Stationer (fält ”Biljettköp-info”).
2. **Villkorlig logik:** Matcha utresans första hållplats mot station-ID eller konfigurerad lista (Uppsala Ö, Marielund, Almunge, fallback).
3. **Datamodell:** Array av `{ id, text, condition: 'always' | 'afternoon' | 'day_ticket' | 'station:<id>' }` i `mrt_settings` eller operatörsspecifik JSON — export/import via `settings.csv`.

**UI:** Ersätt en enda `priceNote`-paragraf med en lista (`<ul>` eller flera `<p>`) under prisblocket; samma texter i PDF (`tripSummaryBuild.ts` / `tripSummaryDocument.ts`).

**Acceptanskriterium:** Jesper kan själv ändra/toggle texter i admin utan koddeploy; rätt stationstext visas för resor från Uppsala Ö, Marielund, Almunge respektive övriga hållplatser.

---

## J16. Enklare zonförklaring

- **Originaltext:** *Texten ”Priset bygger på lägsta giltiga zontal för den valda resan (max tre zoner enligt taxa 2026). Pensionär gäller från 65 år.” låter lite teknisk. Jag föredrar ”Biljettpriset beror på hur många zoner din resa går igenom. Som mest kan en resa gå igenom tre zoner”*
- **Område:** Sammanfattning / prisfotnot
- **Typ:** copy
- **Prioritet:** låg (snabb fix om J15 inte görs samtidigt)
- **Status:** **implementerad** (2026-06-11, commit `cd4edda`) — enklare zoncopy + pensionär som separat rad; ingår i PDF/textexport

### Nuvarande läge

```132:133:inc/assets/frontend.php
		'priceNote'            => __( 'Biljettpriset beror på hur många zoner din resa går igenom. Som mest kan en resa gå igenom tre zoner.', 'museum-railway-timetable' ),
		'priceNoteSenior'      => __( 'Pensionär gäller från 65 år.', 'museum-railway-timetable' ),
```

### Beslut

- [x] Jespers zonformulering i `priceNote`
- [x] Pensionär kvar som separat rad (`priceNoteSenior`)

---

## J17. CTA-knapp — ”Mer information om biljettköp”

- **Originaltext:** *Knappen ”Fortsätt till Biljetter” kan också ändras till ”Mer information om biljettköp”. Nu kan det tolkas som att man kan köpa biljetter genom att klicka på knappen.*
- **Område:** Sammanfattning / primär knapp
- **Typ:** copy / UX
- **Prioritet:** medium
- **Status:** **implementerad** (2026-06-11, commit `350ea96`) — default «Mer information om biljettköp»; admin-hint uppdaterad; E2E justerad

### Nuvarande läge

Knappen visas när `ticket_url` är satt (global inställning eller shortcode). Etikett: `ticketCta` default **«Mer information om biljettköp»** i `vue-shortcode-config.php` / `WizardSummaryStep.vue`. Admin-hint under Biljett-URL förklarar att länken går till infosida, inte checkout.

**Historik:** I [2026-06-01-granskning.md](2026-06-01-granskning.md) J5 beslutades knappen **borttagen** (gav intryck av onlinebokning). Den är **tillbaka** när Lennakatten har `ticket_url` → https://www.lennakatten.se/biljetter/ — vilket förklarar Jespers förvirring.

### Beslut

- [x] Default `ticketCta` → «Mer information om biljettköp»
- [x] Admin-hint under Biljett-URL
- [x] E2E: `wizard-steps.spec.ts` (ingen CTA utan `ticket_url`)

---

## J18. Dela-knapp (SMS, WhatsApp m.m.)

- **Originaltext:** *Funderar på om det går att göra en dela-knapp, så att man kan skicka sin resa på t.ex. SMS eller WhatsApp till en bekant (via mobilens gränssnitt för att dela filer). Vet ej vad som är optimalt filformat för detta dock, det kanske inte fungerar så praktiskt med pdf.*
- **Område:** Sammanfattning / export
- **Typ:** feature — utred
- **Prioritet:** låg–medium
- **Status:** **implementerad** (2026-06-15) — Web Share + urklippsfallback i sammanfattningssteget

### Teknisk bakgrund i kodbasen

| Byggsten | Fil | Status |
|----------|-----|--------|
| Plain-text-sammanfattning | `tripSummaryText.ts` | Finns (`buildTripSummaryText`) — kommenterad för ”accessibility or future export” |
| PDF-export | `useSummaryExport.ts` | Finns |
| Web Share API | — | **Ej implementerad** |

### Rekommenderat format

| Format | För-/nackdelar |
|--------|----------------|
| **Plain text** (Web Share `text`) | Enklast; fungerar i SMS/WhatsApp/e-post; ingen filbilaga |
| **URL med query/hash** | Mottagare öppnar samma resa i planeraren — kräver deep-link-stöd (**finns inte**) |
| **PDF** | Tungt på mobil; Web Share med filer har begränsat stöd (Safari/Chrome varierar) |

**Förslag:** Knapp **”Dela”** som anropar `navigator.share({ title, text })` med `buildTripSummaryText()` när API finns; annars fallback kopiera till urklipp + toast. Ingen PDF i v1.

### Acceptanskriterium

På mobil (iOS/Android) öppnas systemets delningsdialog med läsbar resesammanfattning (tider, sträcka, priser i text).

---

## Prioriterad genomgångslista

| # | ID | Punkt | Insats | Rek. ordning |
|---|-----|-------|--------|--------------|
| 1 | J14 | Seg sökning | Utred + ev. cache/profil | 1 — blockerar förtroende |
| 2 | J17 | CTA-knapp copy | Liten | 2 — snabb win |
| 3 | J16 | Zonförklaring | Liten | 3 — kan slås ihop med J15 |
| 4 | J15 | Biljettinfo + admin | Medel–stor | 4 — produktbeslut datamodell |
| 5 | J18 | Dela-knapp | Medel | 5 — efter J15 copy finns i textexport |

---

## Produktbeslut att ta (innan J15/J18)

| # | Fråga | Alternativ |
|---|-------|------------|
| D19 | Var redigeras biljettcopy? | Inställningar / Priser / per station |
| D20 | Pensionär 65+ i zonförklaring? | Behåll separat rad / ta bort / flytta till kategori-etikett |
| D21 | Eftermiddagsnot — ersätta eller komplettera? | En kombinerad fotnot vs flera rader |
| D22 | Dela — scope v1? | Endast text + Web Share / även PDF / deep link senare |

---

## Nästa steg

- [ ] Jesper: bekräfta vilket steg som är långsamt (J14) — se [svar till Jesper](2026-06-11-svar-till-jesper.md)
- [ ] Team: produktbeslut D19–D22
- [ ] Implementation enligt prioritetslista ovan
- [ ] Uppdatera [TODO.md](../TODO.md) när beslut är tagna

---

## Bilder (denna omgång)

| Beskrivning | Kopplad punkt |
|-------------|---------------|
| Sammanfattning med priser, eftermiddagsbiljett, CTA-knapp | J15, J16, J17 |
| (Ev.) långsam sökning | J14 |

> Skärmdump: `Skärmavbild 2026-06-10 kl. 17.37.08.png` — ev. arkivera i `docs/feedback/images/`.
