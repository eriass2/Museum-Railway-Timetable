# Feedback – Jesper (reseplanerare, juni 2026, omgång 4)

Återkoppling efter publicering och [svar 11 juni](2026-06-11-svar-till-jesper.md). Jesper bekräftar att vi **fixar det som finns nu innan vi bygger vidare** och att reseplaneraren **känns snabbare**. **Gå igenom en punkt i taget** — bocka av status när punkt är besvarad, fixad eller avvisad.

**Källor:** mail/skärmdumpar från Jesper (2026-06-12), mockup `image0.png`  
**Senast uppdaterad:** 2026-06-13 (omgång 4 implementation klar team; test3-verifiering återstår)  
**Relaterat:** [2026-06-11-jesper-reseplanerare.md](2026-06-11-jesper-reseplanerare.md) (J14–J18), [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md) (J3, J4, J10), [2026-06-11-svar-till-jesper.md](2026-06-11-svar-till-jesper.md)

---

## Sammanfattning

| Kategori | Antal | Prioritet | Status |
|----------|-------|-----------|--------|
| Prestanda (bekräftelse) | 1 | — | Jesper OK — J14 upplevs bättre |
| Layout / design (regression + ny riktning) | 3 | hög | J19 klar (väntar Jesper); J20–J21 implementation klar, J21 verifiering test3 senare |
| Detaljvy — buggar (Ca, mot, fotnot) | 3 | hög | J23–J26 klar (team) |
| Detaljvy — polish (tidslinje, Ca-rad) | 2 | medium–låg | J22, J25 klar (team) |
| Omgång 3 kvar (J15–J18) | 4 | varierande | öppen — paus tills layout/buggar fixade |

**Kort tolkning:** Jesper vill (1) grön bakgrund över **hela** planeraren med vit text och vita innehållsrutor (som ursprungsutkastet), (2) stegknappar **bredvid varandra** även på mobil, (3) layoutfixar på hemsidan — fullbreddsbild utan grönt filter, inget utanför skärmen, smalare/kompaktare panel (UL-lik), (4) rätta buggar i ruttdetalj: Ca bara vid behovsuppehåll, rätt tågdestination i ”mot …”, korrekt behovsuppehåll-ikon vid rätt hållplats, samt (5) tidslinjegrafik som åter ser konstig ut.

---

## Kontext (ej separat ticket)

- **Originaltext:** *Jag håller fullständigt med om att vi fixar det som finns nu innan vi bygger vidare. Nu verkar också reseplaneraren gå snabbare.*
- **Tolkning:** J15–J18 (biljettcopy, dela-knapp m.m.) skjuts tills denna omgång är klar.
- **J14:** Jesper bekräftar förbättrad hastighet — inget nytt åtgärdsbehov om inte regression rapporteras.

---

## J19. Grön bakgrund på hela planeraren (inte bara ram)

- **Originaltext:** *Nu blir också den gröna färgen mest som en ram, så jag funderar på om det skulle vara möjligt att ändra bakgrundsfärgen på hela reseplaneraren till grönt, med vit text över. Dock att behålla alla rutor och kalendern med vit bakgrund. Lite som i mitt ursprungliga utkast.*
- **Referens:** Jespers mockup (`image0.png`)
- **Område:** Reseplanerare / layout, alla steg
- **Typ:** design / regression mot ursprungsutkast
- **Prioritet:** hög
- **Status:** **klar (team, 2026-06-13)** — väntar Jespers visuella OK mot mockup. Grön huvudpanel (`MrtWizardMainCard`), vit text på beta/steg/rubriker; formulär, kalender och reskort i vita `MrtSurfaceCard` inuti.

### Nuvarande läge i kod

| Del | Var | Beteende idag |
|-----|-----|---------------|
| Hero / shell | `MrtWizardHero`, `MrtWizardShell` | Grön yta runt innehåll; transparent hero när bleed-backdrop aktiv (desktop) |
| Huvudpanel | `MrtWizardMainCard` | Grön bakgrund (`--mrt-wizard-green-dark`), vit typografi |
| Inre sektioner | `MrtSurfaceCard` i stegkomponenter | Vit bakgrund: sök, kalender, resekort, sammanfattning |
| Söksteg | `WizardRouteStep.vue` | Restyp före Från/Till |
| E2E | `wizard-route-layout.spec.ts` | Grön shell + vit inre route-form |

### Levererat (team)

1. Grön bakgrund på huvudpanel och shell, vit typografi utanför vita kort.
2. Kontrast på tillbaka-länk, stegnavigering och betaband (gul aktiv, vit/grön övrigt).
3. Vita `MrtSurfaceCard` — ingen grön inuti funktionella rutor.

### Kvar (Jesper)

- Visuell bekräftelse mot mockup `image0.png` på test3 (mobil + desktop).
- Ev. finjustering av nyanser/spacing **endast om** Jesper flaggar avvikelse.

### Acceptanskriterium

På mobil och desktop känns hela planeraren grön med vita kort inuti — inte en smal grön ram med vitt runt omkring.

---

## J20. Stegknappar bredvid varandra på mobil

- **Originaltext:** *Jag tycker faktiskt det var snyggare när knapparna överst var bredvid varandra och inte under varandra, som det nu blir på mobilen.*
- **Skärmdump:** stegnavigering (4 steg på rad i äldre version; 2×2-grid i nuvarande mobil)
- **Område:** Reseplanerare / `MrtStepProgress`
- **Typ:** UX / regression
- **Prioritet:** medium
- **Status:** **klar (team, 2026-06-13)** — horisontell rad på mobil (`MrtStepProgress`: `flex` + `nowrap`, sidscroll vid behov).

### Nuvarande läge

`MrtStepProgress.vue` (max-width 48rem): horisontell rad med `flex-wrap: nowrap`; kompaktare font/padding; `overflow-x: auto` på nav vid smala skärmar.

### Acceptanskriterium

Alla fyra stegknappar på **samma rad** på mobil (som tidigare), utan att bryta till 2×2.

---

## J21. Hemsida — overflow, bildbredd, kompakt layout, grönt filter

- **Originaltext:** *På hemsidan blir det väldigt konstigt med flera element utanför skärmen … Bilden sträcker inte över hela skärmen som jag föreslog. Själva reseplaneraren är också väldigt utdragen på bredden. Se gärna om det går att trycka ihop allt, så det blir lite mer kompakt, återigen lite ungefär som UL:s reseplanerare. För övrigt, det verkar ligga något grönt filter över bilden. Det ser jag helst tas bort.*
- **Skärmdumpar:** söksteg + datumsteg med vita marginaler, element utanför viewport
- **Område:** Reseplanerare / hero-layout, WordPress-tema-integration
- **Typ:** bugg + design (J10-uppföljning)
- **Prioritet:** hög
- **Status:** **implementation klar (team, 2026-06-13)** — manuell verifiering på Lennakatten-tema (`t3`) återstår.

### Delpunkter

| # | Problem | Implementation (team) | Verifiering |
|---|---------|----------------------|-------------|
| A | Element utanför skärmen (horisontell overflow) | ☑ `MrtPublicAppShell`, centrerad layout, `min-width: 0` | ☐ test3 mobil/desktop |
| B | Bakgrundsbild täcker inte hela bredden | ☑ `bleedBackground` + `.mrt-app-shell__backdrop` edge-to-edge (≥48rem) | ☑ E2E `wizard-front-page-wp.spec.ts` (WP demo); ☐ test3 |
| C | Planeraren för bred/utdragen | ☑ `--mrt-wizard-content-max: min(76.8vw, 64rem)` | ☐ Jesper/UL-jämförelse; ev. D24 (smalare rem) |
| D | Grönt filter över foto | ☑ Ingen overlay på bleed-backdrop | ☐ test3; embedded shortcode har fortfarande 30 % grön `::before` i `MrtWizardHero` (D26) |
| E | Restyp före destination (söksteg) | ☑ `WizardRouteStep.vue` | ☑ E2E `wizard-route-layout.spec.ts` |

### Levererat i kod

| Komponent | Ändring |
|-----------|---------|
| `MrtPublicAppShell.vue` | Bleed-backdrop utan grön overlay; full viewport-bredd desktop |
| `MrtWizardShell.vue` | `--mrt-wizard-content-max` på innehåll |
| `JourneyWizardApp.vue` | `bleedBackground` när hero-bild + ej embedded |
| `WizardRouteStep.vue` | Restyp före stationfält |

### Kvar (senare)

1. **Manuell check** på `t3.lennakatten.se` — inbäddad shortcode i Lennakatten-tema (inte bara lokal e2e/demo).
2. **Horisontell scroll** — bekräfta ingen sidscroll på mobil/desktop på startsidan.
3. **Mobil &lt;48rem** — full-bleed-foto visas inte (backdrop dold); grön hero + grön huvudpanel — OK om Jesper accepterar, annars utred mobil hero.
4. **D24** — maxbredd idag upp till 64rem; Jesper nämnde UL-lik ~40–48rem — produktbeslut vid behov.
5. **D26** — embedded hero med bakgrundsbild har lätt grön toning (`::before` 30 %) — separat om inbäddade shortcodes ska matcha front page.

### Acceptanskriterium

- Ingen horisontell scroll på mobil/desktop på hemsidan.
- Bakgrundsfoto utan grön toning, kant till kant.
- Sökformulär och steginnehåll visuellt kompaktare (smalare än idag).

---

## J22. Rutttgrafik ser konstig ut igen

- **Originaltext:** *Nu ser grafiken över rutten konstig ut igen, se bilden nedan.*
- **Skärmdump:** mobil detaljvy — vertikal linje, cirkel-noder, tider till vänster
- **Område:** Reseplanerare / `MrtTimeline`, `WizardTimeline`
- **Typ:** regression (J3 fixad 2026-06-09)
- **Prioritet:** medium–hög
- **Status:** **klar (team, 2026-06-13)** — vertikal linje per rad i `mrt-timeline__node-col` (centrerad i nodkolumnen); verifiera på test3 mobil.

### Rotorsak (var)

Global `::before`-linje med fast `top`/`left` följde inte nodkolumnen när radhöjd och `--mrt-tl-node` varierade (container queries + `margin-top` på nod).

### Levererat (team)

| Del | Var | Beteende |
|-----|-----|----------|
| Linje | `MrtTimeline.vue` — `mrt-timeline__node-col::before` | Segment per rad, centrerat i nodkolumn |
| Nod | `mrt-timeline__node` | Ingen `margin-top`; `z-index` ovan linje |
| Mobil | `MrtDetailPanel.vue` | Uppdaterad nodkolumn-styling |

### Acceptanskriterium

Vertikal linje centrerad genom stationsymboler; inga brutna eller förskjutna noder (som J3).

---

## J23. ”Ca” visas fel — bara vid behovsuppehåll

- **Originaltext:** *Det är också fel att Uppsala Ö är en cirkatid, det ska bara gälla uppehåll som är behovsuppehåll (detta bör man alltså ange i turens stopptider). Hur jag menar framgår av min mockup ovan.*
- **Skärmdump:** Uppsala Östra visar ”Ca 10.00” på direktresa Uppsala Ö → Lövstahagen
- **Område:** Reseplanerare / detaljvy + admin stopptider
- **Typ:** bugg (logik och/eller data)
- **Prioritet:** hög
- **Status:** **klar (team, 2026-06-12)** — Ca i reseplaneraren endast vid `approximate_time` **och** behovsläge (`on_request`); enhetstester i `StopTimeWizardDisplayTest`. Verifiera på test3 (Uppsala Ö → Lövstahagen).

### Rotorsak (var)

Ca-prefix visades när `approximate_time` var satt — oavsett schemalagt eller behov — enligt äldre J4-tolkning (Ca för hållplatser utan exakt B-tid).

### Levererat (team)

| Del | Var | Beteende |
|-----|-----|----------|
| Wizard-tidsrad | `stop-time-wizard-display.php` | `show_ca = approximate_time && (pickup_or \|\| dropoff_or)` |
| Test | `StopTimeWizardDisplayTest` | Schemalagd + `approximate_time` → ingen Ca; behov + `approximate_time` → `Ca …` |

### Kvar (ev. senare)

| Del | Status |
|-----|--------|
| Tidtabellsöversikt / Turvy | `stop-time-display.php` — fortfarande Ca enbart från `approximate_time` (produktbeslut D23) |
| Admin-hjälptext | Ej uppdaterad — operatörsguide för när Ca ska kryssas i |
| `docs/STOP_TIME_CA.md` | Beskriver gammal semantik; synka om D23 beslutas |

### Acceptanskriterium

Uppsala Östra visar **10.00** (utan Ca) på exemplet; behovshållplatser enligt mockup.

---

## J24. Fel ”mot”-destination på tågetikett

- **Originaltext:** *Vidare är ju detta inte ångtåg 81 mot Lövstahagen, bara för att man åker till Lövstahagen. Det ska ju stå den destination som tåget går till, exempelvis Marielund eller Faringe.*
- **Skärmdump:** ”Ångtåg 81 mot Lövstahagen” i detaljvy
- **Område:** Reseplanerare / fordonsrad, API
- **Typ:** bugg
- **Prioritet:** hög
- **Status:** **klar (team, 2026-06-12)** — leg-`destination` från tjänstens slutstation via `MRT_journey_service_destination_label()`; enhetstest `JourneyMultiLegTest::test_journey_leg_destination_uses_service_end_not_passenger_alighting`. Verifiera på test3 (Uppsala Ö → Lövstahagen, ångtåg 81).

### Rotorsak (var)

Leg-`destination` sattes tidigare via `MRT_journey_leg_destination_label( $to_station_id )` — passagerarens avstigning — medan `vehicle.ts` visar `leg.destination` som ”mot …”.

### Levererat (team)

| Del | Var | Beteende |
|-----|-----|----------|
| Leg-byggare | `journey-multi-leg.php` | `MRT_journey_service_destination_label( $service_id )` |
| Hjälpare | `train-change.php` | `MRT_journey_service_destination_label()` → `MRT_get_service_destination()` |
| Test | `JourneyMultiLegTest` | Resa till Lövstahagen → destination Marielund |
| Integration | `LennakattenJourneySearchTest` | Uppsala Ö → Marielund direkt med korrekt etikett |

### Acceptanskriterium

Ångtåg 81 på resa till Lövstahagen visar t.ex. **”Ångtåg 81 mot Marielund”** (eller faktisk slutdestination för turen).

---

## J25. Radbrytning ”Ca 10.00” — vertikal centrering

- **Originaltext:** *Det är inte jättesnyggt med radbrytning på ”Ca 10.00”, men det kanske är nödvändigt av utrymmesskäl. Åtminstone hade det varit snyggt om det var centrerat i höjdled mot symbolen för stationen.*
- **Område:** Reseplanerare / tidslinje CSS
- **Typ:** polish
- **Prioritet:** låg–medium
- **Status:** **klar (team, 2026-06-13)** — `Ca` staplas ovanför klockslag; rad `align-items: center` mot nod.

### Levererat (team)

| Del | Var | Beteende |
|-----|-----|----------|
| Ca-layout | `MrtTimeline.vue` — `mrt-timeline__time-stack` | `Ca` på egen rad ovanför tid |
| Vertikal centrering | `mrt-timeline__row` | `align-items: center` |

### Acceptanskriterium

Vid radbrytning ligger tid+cirkaprefix visuellt centrerat mot stationscirkeln.

---

## J26. Behovsuppehåll — fel anmärkning och fel presentation

- **Originaltext:** *Anmärkningen blir också konstig, inte riktigt vad jag tänkte. Den verkar hänvisa till Fyrislund och Årsta, som resan inte berörs av. Min tanke var att det, i det här fallet, vid Lövstahagen ska finnas en infoikon ℹ️, som hänvisar till anmärkningen under: ”ℹ️ Behovsuppehåll, säg till konduktören i god tid om du vill stiga av”. Det här kan du också se i min mockup ovan.*
- **Skärmdump:** P-text om påstigning vid Fyrislund/Årsta; Jesper vill ℹ️ vid Lövstahagen + avstigningstext
- **Område:** Reseplanerare / detaljvy, fotnoter
- **Typ:** bugg + UX (A3/J4-uppföljning)
- **Prioritet:** hög
- **Status:** **klar (team, 2026-06-13)** — fotnoter endast vid passagerarens av-/påstigning (API); ℹ️ i tidslinje + fotnotlista. Verifiera på test3 (Uppsala Ö → Lövstahagen).

### Rotorsak (var)

P/A-fotnoter samlades från **alla** stopp i segmentet, inkl. mellanstationer med behov-påstigning (Fyrislund, Årsta) som passageraren bara passerar.

### Levererat (team)

| Del | Var | Beteende |
|-----|-----|----------|
| API-flaggor | `stop-time-display.php` | Avstigningsfotnot endast vid `is_last_in_leg`; inga P-fotnoter i wizard |
| Tidslinje | `MrtTimeline.vue` | ℹ️ vid stopp med behovsuppehåll |
| Fotnotlista | `stopTimeFootnotes.ts`, `WizardTripDetail.vue` | ℹ️ + av-/påstigningstext, deduplicerad |
| Test | `JourneyDetailTest`, `StopTimeWizardDisplayTest`, Vue unit | Mellanstation utan ℹ️; Lövstahagen med avstigning |

### Acceptanskriterium

Resa till Lövstahagen: ℹ️ vid Lövstahagen, fotnot om avstigning enligt mockup — **ingen** fotnot om Fyrislund/Årsta.

---

## Prioriterad genomgångslista

| # | ID | Punkt | Insats | Rek. ordning |
|---|-----|-------|--------|--------------|
| 1 | J19 | Grön bakgrund hela planeraren | Medel | **klar team** — Jesper OK |
| 2 | J21 | Overflow, bild, kompakt, filter | Medel | **klar team** — verifiera test3 |
| 3 | J20 | Stegknappar på rad mobil | Liten (CSS) | **klar team** — Jesper OK |
| 4 | J24 | Fel ”mot”-destination | Liten (PHP) | **klar team** — verifiera test3 |
| 5 | J23 | Ca bara behovsuppehåll | Liten–medel | **klar team** — verifiera test3 |
| 6 | J26 | Behovsuppehåll ℹ️ per stopp | Medel | **klar team** — verifiera test3 |
| 7 | J22 | Rutttgrafik regression | Liten–medel | **klar team** — verifiera test3 |
| 8 | J25 | Ca vertikal centrering | Liten (CSS) | **klar team** — verifiera test3 |

**Pausad tills ovan är klart:** J15–J18 ([omgång 3](2026-06-11-jesper-reseplanerare.md)).

---

## Produktbeslut att ta

| # | Fråga | Alternativ |
|---|-------|------------|
| D23 | Ca-semantik framåt | A) Endast behovsuppehåll · B) Behåll `approximate_time` för tidtabell · C) Båda (tydlig prioritet) |
| D24 | Maxbredd innehåll (UL-lik) | Fast rem (t.ex. 40/48) vs responsiv % |
| D25 | Behovsinformation | ℹ️ per stopp + fotnot · behåll P/A · hybrid |
| D26 | Hero-bild utan filter | Helt utan overlay vs mycket lätt toning för läsbarhet |

---

## Nästa steg

- [x] J19: grön huvudpanel, vit text, vita inre rutor (2026-06-12) — **klar team; väntar Jesper**
- [x] J21 implementation: shell full-bleed, filter bort backdrop, maxbredd, restyp före stationer (2026-06-12)
- [x] J20: stegknappar horisontell rad på mobil (2026-06-13)
- [ ] **J21 verifiering:** manuell check på `t3.lennakatten.se` (tema + shortcode, mobil + desktop)
- [ ] Jesper: visuellt OK J19 (+ ev. J20/J21 efter test3)
- [ ] Team: produktbeslut D23–D26 (vid behov efter test3)
- [ ] Implementation J22–J26 enligt prioritetslista
- [ ] J15–J18: återuppta efter Jesper bekräftat att omgång 4 layout är OK

---

## Bilder (denna omgång)

| Beskrivning | Kopplad punkt |
|-------------|---------------|
| Söksteg — bred panel, grön ram, bakgrund med filter | J19, J21 |
| Datumsteg — vit marginal, steg på rad (desktop) | J21 |
| Detaljvy desktop — kalender i grön ram | J19 |
| Detaljvy mobil — Ca vid Uppsala Ö, fel ”mot”, P-fotnot, rutttgrafik | J22–J26 |
| Jespers mockup (`image0.png`) | J19, J23, J26 |

> Skärmdumpar från Jespers mail 2026-06-12; ev. arkivera i `docs/feedback/images/`.
