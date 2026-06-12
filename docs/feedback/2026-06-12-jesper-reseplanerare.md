# Feedback – Jesper (reseplanerare, juni 2026, omgång 4)

Återkoppling efter publicering och [svar 11 juni](2026-06-11-svar-till-jesper.md). Jesper bekräftar att vi **fixar det som finns nu innan vi bygger vidare** och att reseplaneraren **känns snabbare**. **Gå igenom en punkt i taget** — bocka av status när punkt är besvarad, fixad eller avvisad.

**Källor:** mail/skärmdumpar från Jesper (2026-06-12), mockup `image0.png`  
**Senast uppdaterad:** 2026-06-12 (J19/J21 layout implementerad)  
**Relaterat:** [2026-06-11-jesper-reseplanerare.md](2026-06-11-jesper-reseplanerare.md) (J14–J18), [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md) (J3, J4, J10), [2026-06-11-svar-till-jesper.md](2026-06-11-svar-till-jesper.md)

---

## Sammanfattning

| Kategori | Antal | Prioritet | Status |
|----------|-------|-----------|--------|
| Prestanda (bekräftelse) | 1 | — | Jesper OK — J14 upplevs bättre |
| Layout / design (regression + ny riktning) | 3 | hög | delvis åtgärdad (J19/J21 layout) |
| Detaljvy — buggar (Ca, mot, fotnot) | 3 | hög | öppen |
| Detaljvy — polish (tidslinje, Ca-rad) | 2 | medium–låg | öppen |
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
- **Status:** **delvis åtgärdad** (2026-06-12) — grön huvudpanel (`mrt-journey-wizard__main-card`) med vit text; beta, steg och rubriker på grönt; formulär, kalender och reskort i vita rutor inuti. Kvar: ev. finjustering mot mockup.

### Nuvarande läge i kod

| Del | Var | Beteende idag |
|-----|-----|---------------|
| Yta utanför kort | `app-shell.css` — backdrop / hero | Grön eller foto full bredd |
| Huvudpanel | `JourneyWizardApp.vue` — `__main-card` | Vit `MrtSurfaceCard`: beta, steg, alla steg |
| Inre sektioner | datum/utresa/summary | Vit `--box` inuti huvudpanel vid behov |
| Söksteg | `WizardRouteStep.vue` | Restyp före Från/Till |

### Önskat utseende (Jesper)

- Hela reseplanerarens yta = mörkgrön med vit text/etiketter.
- Alla funktionella rutor (sök, kalender, resekort, detalj) = vit bakgrund oförändrad.
- Som ursprungsmockup — inte grön bara som kant runt innehållet.

### Föreslagen åtgärd

1. Grön bakgrund på wizard-shell (hero + stegområde), vit typografi på rubriker/kontext utanför vita kort.
2. Säkerställ kontrast på tillbaka-länk, stegnavigering och betaband.
3. Behåll vita `MrtSurfaceCard` — ingen grön inuti kort.

### Acceptanskriterium

På mobil och desktop känns hela planeraren grön med vita kort inuti — inte en smal grön ram med vitt runt omkring.

---

## J20. Stegknappar bredvid varandra på mobil

- **Originaltext:** *Jag tycker faktiskt det var snyggare när knapparna överst var bredvid varandra och inte under varandra, som det nu blir på mobilen.*
- **Skärmdump:** stegnavigering (4 steg på rad i äldre version; 2×2-grid i nuvarande mobil)
- **Område:** Reseplanerare / `MrtStepProgress`
- **Typ:** UX / regression
- **Prioritet:** medium
- **Status:** öppen

### Nuvarande läge

`responsive.css` (max-width 48rem): `.mrt-step-progress` är **2-kolumns grid** med sista udda knapp centrerad — ger radbrytning 2+2 istället för en horisontell rad.

### Föreslagen åtgärd

- Återgå till horisontell rad (flex/grid 4 kolumner eller scrollbara chips) på mobil.
- Ev. mindre font/padding för att få plats — Jesper föredrar rad framför stapling.
- Testa smala skärmar (&lt;360px) — ev. horisontell scroll som fallback.

### Acceptanskriterium

Alla fyra stegknappar på **samma rad** på mobil (som tidigare), utan att bryta till 2×2.

---

## J21. Hemsida — overflow, bildbredd, kompakt layout, grönt filter

- **Originaltext:** *På hemsidan blir det väldigt konstigt med flera element utanför skärmen … Bilden sträcker inte över hela skärmen som jag föreslog. Själva reseplaneraren är också väldigt utdragen på bredden. Se gärna om det går att trycka ihop allt, så det blir lite mer kompakt, återigen lite ungefär som UL:s reseplanerare. För övrigt, det verkar ligga något grönt filter över bilden. Det ser jag helst tas bort.*
- **Skärmdumpar:** söksteg + datumsteg med vita marginaler, element utanför viewport
- **Område:** Reseplanerare / hero-layout, WordPress-tema-integration
- **Typ:** bugg + design (J10-uppföljning)
- **Prioritet:** hög
- **Status:** **delvis åtgärdad** (2026-06-12) — se delpunkter nedan.

### Delpunkter

| # | Problem | Status (2026-06-12) |
|---|---------|---------------------|
| A | Element utanför skärmen (horisontell overflow) | ☑ `MrtPublicAppShell` + centrerad layout |
| B | Bakgrundsbild täcker inte hela bredden | ☑ backdrop full viewport |
| C | Planeraren för bred/utdragen | ☑ `--mrt-wizard-content-max` + vit huvudpanel |
| D | Grönt filter över foto | ☑ overlay borttagen från backdrop |
| E | Restyp före destination (söksteg) | ☑ `WizardRouteStep.vue` |

### Föreslagen åtgärd

1. **Ta bort eller kraftigt minska** grön `::before`-overlay på hero-bild.
2. **Overflow-audit** — `overflow-x: hidden` på rätt nivå eller fixa 100vw-beräkning (scrollbar-gutter).
3. **Maxbredd** på innehåll — UL-lik (~40–48rem centrerat?) istället för full viewport-bredd på paneler.
4. **Bild full bleed** — hero-bakgrund ska nå viewport-kanter (J10-intention); innehåll centrerat smalt.
5. Verifiera i **inbäddad shortcode på Lennakatten-tema**, inte bara ren shortcode-sida.

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
- **Status:** öppen

### Historik

J3 (2026-06-09): nodbredd vs `--mrt-tl-node`, `justify-self: center` — markerad **klar**.

### Möjliga orsaker (utred)

- Mobil CSS i `responsive.css` / timeline-styles efter J10-heroändringar.
- Radbrytning på tidskolumn (J25) som skjuter layout.
- Regression i `MrtTimeline.vue` eller `assets/frontend/ui/timeline.css`.

### Föreslagen åtgärd

1. Jämför med skärmdump före J10-leverans.
2. Verifiera nod/linje-centrering på mobil och desktop.
3. E2E/screenshot-test om möjligt.

### Acceptanskriterium

Vertikal linje centrerad genom stationsymboler; inga brutna eller förskjutna noder (som J3).

---

## J23. ”Ca” visas fel — bara vid behovsuppehåll

- **Originaltext:** *Det är också fel att Uppsala Ö är en cirkatid, det ska bara gälla uppehåll som är behovsuppehåll (detta bör man alltså ange i turens stopptider). Hur jag menar framgår av min mockup ovan.*
- **Skärmdump:** Uppsala Östra visar ”Ca 10.00” på direktresa Uppsala Ö → Lövstahagen
- **Område:** Reseplanerare / detaljvy + admin stopptider
- **Typ:** bugg (logik och/eller data)
- **Prioritet:** hög
- **Status:** öppen

### Nuvarande läge

| Del | Var | Logik |
|-----|-----|-------|
| Ca-prefix | `stop-time-wizard-display.php` | `approximate_time` på stoprad → `Ca ` + tid |
| Admin | Stopptider — checkbox `approximate_time` | Separat från behovsläge (`on_request`) |
| J4 (2026-06-09) | Ca för hållplatser utan exakt tid i tidtabell | Kan krocka med Jespers nya tolkning |

### Jespers intention

- **Uppsala Ö** (schemalagd avgång) ska **inte** ha Ca.
- **Ca** ska gälla **behovsuppehåll** — anges via turens stopptider (behov + ev. ungefärlig tid).
- Mockup visar exakt tid vid ordinarie hållplatser; Ca vid behovshållplatser.

### Föreslagen åtgärd

1. **Data:** Kontrollera om Uppsala Ö har `approximate_time` felaktigt satt i Lennakatten-data.
2. **Produkt/logik:** Klargör om Ca = `approximate_time` **eller** Ca = behovsuppehåll med tid — Jesper säger senare.
3. Om ny regel: visa Ca endast när `on_request` + tid (eller enbart behov enligt mockup), inte för schemalagda stationer.
4. Uppdatera admin-hjälptext under stopptider så operatör vet när Ca ska användas.

### Acceptanskriterium

Uppsala Östra visar **10.00** (utan Ca) på exemplet; behovshållplatser enligt mockup.

---

## J24. Fel ”mot”-destination på tågetikett

- **Originaltext:** *Vidare är ju detta inte ångtåg 81 mot Lövstahagen, bara för att man åker till Lövstahagen. Det ska ju stå den destination som tåget går till, exempelvis Marielund eller Faringe.*
- **Skärmdump:** ”Ångtåg 81 mot Lövstahagen” i detaljvy
- **Område:** Reseplanerare / fordonsrad, API
- **Typ:** bugg
- **Prioritet:** hög
- **Status:** öppen

### Rotorsak (kod)

`journey-multi-leg.php` sätter leg-`destination` via `MRT_journey_leg_destination_label( $to_station_id )` — dvs **resans sluthållplats** (passagerarens mål), inte **tjänstens slutdestination**.

`train-change.php`:

```php
function MRT_journey_leg_destination_label( int $to_station_id ): string {
    return get_the_title( $to_station_id ); // passagerarens avstigning
}
```

`vehicle.ts` — `legTowardsSuffix(leg.destination)` → ”mot Lövstahagen”.

**Korrekt källa:** `MRT_get_service_destination( $service_id )` (används redan i `journey-normalize-segments.php`).

### Föreslagen åtgärd

1. Byt `destination` på leg-objekt till tjänstens destination (sista station på linjen / angiven i admin).
2. Lägg till/enhetstest: resa till mellanliggande hållplats ska visa ”mot Marielund” (eller Faringe), inte mellanliggande namn.
3. Verifiera flerbenade resor — varje ben sitt tågs destination.

### Acceptanskriterium

Ångtåg 81 på resa till Lövstahagen visar t.ex. **”Ångtåg 81 mot Marielund”** (eller faktisk slutdestination för turen).

---

## J25. Radbrytning ”Ca 10.00” — vertikal centrering

- **Originaltext:** *Det är inte jättesnyggt med radbrytning på ”Ca 10.00”, men det kanske är nödvändigt av utrymmesskäl. Åtminstone hade det varit snyggt om det var centrerat i höjdled mot symbolen för stationen.*
- **Område:** Reseplanerare / tidslinje CSS
- **Typ:** polish
- **Prioritet:** låg–medium
- **Status:** öppen

### Föreslagen åtgärd

- Tidskolumn: `align-items: center` mot nodrad, eller `Ca` som egen liten rad ovanför tiden med gemensam vertikal centrering.
- Ev. `white-space: nowrap` på smala skärmar om plats finns efter J21-kompaktning.

### Acceptanskriterium

Vid radbrytning ligger tid+cirkaprefix visuellt centrerat mot stationscirkeln.

---

## J26. Behovsuppehåll — fel anmärkning och fel presentation

- **Originaltext:** *Anmärkningen blir också konstig, inte riktigt vad jag tänkte. Den verkar hänvisa till Fyrislund och Årsta, som resan inte berörs av. Min tanke var att det, i det här fallet, vid Lövstahagen ska finnas en infoikon ℹ️, som hänvisar till anmärkningen under: ”ℹ️ Behovsuppehåll, säg till konduktören i god tid om du vill stiga av”. Det här kan du också se i min mockup ovan.*
- **Skärmdump:** P-text om påstigning vid Fyrislund/Årsta; Jesper vill ℹ️ vid Lövstahagen + avstigningstext
- **Område:** Reseplanerare / detaljvy, fotnoter
- **Typ:** bugg + UX (A3/J4-uppföljning)
- **Prioritet:** hög
- **Status:** öppen

### Nuvarande läge

| Del | Var | Beteende |
|-----|-----|----------|
| P/A-markörer | `stopTimeFootnotes.ts`, `MrtTimeline.vue` | Superscript P/A vid station |
| Global fotnotlista | `WizardTripDetail.vue` — `mrt-detail-footnotes` | Dedup per P/A över **alla** stopp i resan |
| Påstigningstext | `frontend.php` `onRequestPickupFootnote` | ”…ge ett tecken till föraren om du vill stiga på.” |
| Avstigningstext | `onRequestDropoffFootnote` | ”…säg till konduktören i god tid om du vill stiga av.” |

**Problem:** P-fotnot triggas av behov-påstigning på **andra** hållplatser längs tjänsten (Fyrislund, Årsta) som inte ingår i passagerarens synliga resa, eller visas globalt utan koppling till rätt stopp.

### Jespers önskade UX (mockup)

- **ℹ️-ikon vid aktuell hållplats** (här: Lövstahagen vid avstigning).
- **En fotnot under rutten:** ”ℹ️ Behovsuppehåll, säg till konduktören i god tid om du vill stiga av”.
- Ingen missvisande P-fotnot för hållplatser resan inte berör.

### Föreslagen åtgärd

1. Visa behovsinformation **per stopp** i tidslinjen (ℹ️), inte enbart global P/A-lista.
2. Begränsa fotnoter till stopp som **faktiskt visas** i passagerarens segment (inkl. expanderade passerade).
3. Vid avstigning på behovshållplats: avstigningstext; vid påstigning: påstigningstext — kopplat till rätt stopp.
4. Uppdatera copy till Jespers formulering med ℹ️ i fotnotraden.
5. Överväg att ta bort eller ersätta generiska P/A-superscript om ℹ️ räcker.

### Acceptanskriterium

Resa till Lövstahagen: ℹ️ vid Lövstahagen, fotnot om avstigning enligt mockup — **ingen** fotnot om Fyrislund/Årsta.

---

## Prioriterad genomgångslista

| # | ID | Punkt | Insats | Rek. ordning |
|---|-----|-------|--------|--------------|
| 1 | J21 | Overflow, bild, kompakt, filter | Medel | **delvis klar** — verifiera på test3 |
| 2 | J19 | Grön bakgrund hela planeraren | Medel | **delvis klar** — grön huvudpanel, vit text, vita inre rutor |
| 3 | J24 | Fel ”mot”-destination | Liten (PHP) | 3 — tydlig bugg |
| 4 | J23 | Ca bara behovsuppehåll | Liten–medel | 4 — logik + ev. data |
| 5 | J26 | Behovsuppehåll ℹ️ per stopp | Medel | 5 — UX + logik |
| 6 | J20 | Stegknappar på rad mobil | Liten (CSS) | 6 |
| 7 | J22 | Rutttgrafik regression | Liten–medel | 7 — efter layout |
| 8 | J25 | Ca vertikal centrering | Liten (CSS) | 8 — polish |

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

- [x] J19/J21 layout: grön huvudpanel (vit text), shell full-bleed, filter bort, restyp före stationer (2026-06-12)
- [ ] Team: produktbeslut D23–D26
- [ ] Implementation enligt prioritetslista
- [ ] Verifiera på `t3.lennakatten.se` (tema + shortcode), inte bara lokal demo
- [ ] Uppdatera [TODO.md](../TODO.md) när arbete påbörjas
- [ ] J15–J18: återuppta efter Jesper bekräftat att omgång 4 är OK

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
