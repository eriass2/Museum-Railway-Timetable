# Trafikstörningar v2 — UL-referens och Jesper J11

**Status:** plan (produktbeslut D16 delvis öppen)  
**Källa:** [2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) J11, [2026-06-09-jesper-diskussioner.md](feedback/2026-06-09-jesper-diskussioner.md) D16  
**Föregångare (v1, klar):** [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md) — shortcode + generella meddelanden + dagens tur-avvikelser

---

## 1. Syfte

Jesper efterfrågar en **UL-lik** trafikstörningsvy. Detta dokument beskriver:

1. **Vad UL gör** (referens för operatör och utveckling)
2. **Vad Lennakatten-pluginet redan har** (v1)
3. **Tolkning** — vad J11 sannolikt betyder i vår kontext
4. **Gap** som v2 skulle behöva täcka (innan implementation)

---

## 2. Vad UL gör (referens)

[UL](https://www.ul.se/) (Uppsala lokaltrafik) är Jespers referens — regional kollektivtrafik (buss + pendeltåg/Mälartåg), inte museum-järnväg. Mönstret är ändå tydligt och överförbart.

### 2.1 Publik webb — «Aktuellt» och trafikinformation

| UL-element | Hur det fungerar |
|------------|------------------|
| **Aktuellt** | Kronologisk feed med **datum + rubrik**. Rubriken nämner ofta **vilka linjer** som berörs, t.ex. *«Hållplatsen X indragen för linjerna 3, 4, 8, 13 …»*, *«Buss ersätter … sträckan Uppsala–Sala 11 augusti–5 oktober»*. |
| **Trafikinformation** | Samlade **pågående och kommande** banarbeten / större projekt — inte bara «idag». Tidsintervall (från–till) och **geografisk/sträcka**-beskrivning. |
| **Detaljsida per händelse** | Längre förklaring: orsak, omledning, vilka linjer/tåg, hur länge. |
| **Horisont** | Veckor till månader framåt (planerade arbeten), plus akuta nyheter samma dag. |

Exempel från UL:s aktuellt-flöde (förenklat):

- *2026-03-20 — Hållplatsen Akademiska södra indragen för **linjerna 3, 4, 8, 13, 100, 101 …** tills vidare*
- *2025-10-15 — **All tågtrafik inställd** till och från Uppsala 24–27 oktober*
- *2025-09-29 — **Buss ersätter** vid banarbete … **sträckan Uppsala–Arlanda/Märsta***

**UL:s princip:** Resenären ska snabbt se *vad*, *när*, *var* och **vilka linjer/turer** som drabbas — utan att öppna tidtabellen.

### 2.2 UL-appen / reseplanerare

| UL-element | Hur det fungerar |
|------------|------------------|
| **Störning vid sökresultat** | Trafikstörningar visas **tillsammans med reseförslag** — inte bara på en separat infosida. |
| **Favoriter** | Sparade resor visar om det finns störning **på just den resan**. |
| **Tidsuppdatering** | Vid försening: gammal tid överstruken, ny tid visas (realtime — utöver vår scope). |

Jesper nämner uttryckligen **UI för trafikstörningar** och **UL-lik lista** — primärt en **informationslista**, men UL-appen visar att koppling till **reseplaneraren** också är en naturlig förlängning (D16 öppen).

### 2.3 UL vs Lennakatten — översättning

| UL | Lennakatten (vår domän) |
|----|-------------------------|
| Linje 3, 4, 811 | **Tågnummer / tur** (71, 96, B3 …) eller **rutt/sträcka** (Uppsala–Faringe) |
| Hållplats indragen | Station stängd / ingen stopp |
| Buss ersätter tåg | Tur-avvikelse: ersättningsbuss, inställt |
| Banarbete 11 aug–5 okt | Planerad störning med **datumintervall** + berörda turer |

---

## 3. Vad vi har idag (v1)

Se [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md). Kort:

| Del | Beteende |
|-----|----------|
| **Generella meddelanden** | Admin `#/traffic-notices` — fri text, datum från/till, sortering. |
| **Tur-avvikelser** | Admin **Tidtabell → Avvikelser** — per tur och **datum** (inställt, ersättningstyp, text). |
| **Publik shortcode** | `[museum_traffic_notices]` — punktlista på startsidan: generellt först, sedan avvikelser **idag** (ev. imorgon med `days="2"`). |
| **Format avvikelse** | T.ex. *«Inställd — Tåg 71, Faringe – Uppsala Östra»* — **ett tåg per rad**. |
| **Reseplaneraren** | Visar **inte** automatisk störningslista; avvikelse kan påverka sökresultat indirekt via data. |
| **Turvy / PDF** | Avvikelser markeras per kolumn (inställt, notis). |

**Styrkor v1:** Turkoppling finns i datamodellen; operatör kan redan mata in störningar.  
**Svagheter vs UL:** Ingen samlad «aktuellt»-feed med **kommande** veckor; ingen tydlig **gruppering av flera tåg** i en störning; wizard saknar UL-lik varning; admin har **två begrepp** (trafikmeddelande vs tur-avvikelse).

---

## 4. Jespers ord (J11)

> *«UI för trafikstörningar bör utvecklas — lista på nuvarande och kommande störningar, ange vilka tåg som berörs (likt UL).»*

Nyckelfraser:

| Fras | Tolkning |
|------|----------|
| **nuvarande och kommande** | Inte bara idag/imorgon — **planerade** arbeten och framtida datum (UL: banarbeten med intervall). |
| **ange vilka tåg som berörs** | Tydlig **tur/tågnummer-lista** (UL: «linjerna 3, 4, 8 …»). Idag: en tur per avvikelse-rad, ingen samlad störning. |
| **likt UL** | Strukturerad **lista/feed** med datum, rubrik, berörda turer — inte bara enkla punkter under kalendern. |
| **UI … utvecklas** | Publik vy **och** troligen enklare/redare admin-upplevelse (J11: «Admin + publik visning»). |

Jesper säger **inte** uttryckligen: live-spårning, karta, push-notiser, eller realtime-förseningar (UL-app). Prioritet i beta-listan: **låg**, **parkerad**.

---

## 5. Tolkning — vad Jesper sannolikt vill ha (v2-mål)

Utifrån J11 + UL-referens + v1-gap — **hypotes** att validera med Jesper:

### 5.1 Publik «trafikinfo»-lista (huvudbehov)

En sida eller sektion (startsida eller egen undersida) som liknar UL **Aktuellt**:

- **Lista** sorterad på datum (pågående först, sedan kommande)
- Varje post har:
  - **Rubrik** (kort, självförklarande)
  - **Datum/intervall** («24–27 oktober», «från 7 april tills vidare»)
  - **Berörda tåg/turer** — uttryckligen, t.ex. «Tåg 71, 73, 96» eller «All trafik Faringe–Uppsala»
  - **Kort brödtext** + ev. länk till mer info
- Typer som UL: **inställt**, **ersättningsbuss**, **hållplats ändrad**, **planerat banarbete**, **generell info**

**Inte nödvändigtvis:** UL:s fulla nyhets-CMS, kartor, realtime.

### 5.2 Kommande störningar (tidshorisont)

v1 visar max **2 dagar** (`days="1|2"`). UL visar **månader** framåt.

Jesper vill troligen:

- Se **planerade** avvikelser som operatören redan lagt in för framtida datum
- Ev. **generella meddelanden** med `active_from`/`active_to` över längre perioder (delvis redan möjligt i v1 — men presenteras som flat lista utan «kommande»-gruppering)

### 5.3 Berörda tåg tydligt

Idag: en avvikelse = **en tur** × **ett datum**. UL: en **händelse** kan nämna **många linjer** i samma rubrik.

Jesper vill troligen minst:

- Publik text där **tågnummer syns i rubriken eller i en tag-lista**
- För operatör: antingen flera turer kopplade till samma «störning», eller smart gruppering vid visning

*(Datamodell beslut — se §6.)*

### 5.4 Reseplaneraren (sekundärt / fas 2?)

UL visar störning **vid sökresultat**. Jesper nämnde inte wizard uttryckligen i J11, men D16 frågar om det.

**Mild tolkning (v2a):** Banner eller länk «Se trafikstörningar» i planeraren.  
**UL-lik tolkning (v2b):** Varning på träff om valt datum/tur har avvikelse.

Rekommendation: **v2a först** (lista + shortcode/sida), **v2b** efter avstämning.

### 5.5 Admin (operatör)

Jesper upplevde friktion i **inmatning** generellt (A0). För J11 specifikt:

- Tydligare **en portal** för «vad ska resenären veta?» — idag split: Trafikmeddelanden vs Tidtabell → Avvikelser
- Audit rekommenderade redan förklarande text (UX-2.7 ☑) — men inte UL-lik **översikt**

Troligt önskemål: **en lista** i admin med samma logik som publik vy (nu + kommande), med länk till redigering av underliggande tur-avvikelse eller generellt meddelande.

---

## 6. Gap: v1 → Jesper/UL

| UL / Jesper | v1 idag | Gap |
|-------------|---------|-----|
| Aktuellt-feed med datum i rubrik | Punktlista, ingen rubrik/datum per rad för avvikelser | Presentationslager |
| Kommande veckor/månader | Max 2 dagar | Utöka datumintervall i API/shortcode |
| Flera linjer/tåg per störning | En tur per avvikelse-rad | Gruppering eller ny entitet «störning» |
| Planerade banarbeten (intervall) | Generellt meddelande med from/to **eller** många enstaka tur-datum | Bättre mallar / gruppering |
| Störning i reseplanerare | Saknas | Wizard-integration (fas 2) |
| En admin-översikt | Två menyer/flikar | Ev. samlad «Trafikinfo»-vy |

---

## 7. Öppna produktfrågor (D16)

Innan implementation — **bekräfta med Jesper/operatör**:

| # | Fråga | Förslag (default att diskutera) |
|---|--------|----------------------------------|
| 1 | **Var publiceras listan?** | Utöka shortcode + ev. egen WP-sida «Trafikstörningar»; behåll ovanför kalender på startsidan |
| 2 | **Tidshorisont?** | Visa alla aktiva + kommande inom **90 dagar** (konfigurerbart) |
| 3 | **Datamodell** | **B) Utöka presentation** — behåll tur-avvikelser + generella meddelanden; gruppera i API till «händelser» utan ny CPT i v2a |
| 4 | **Wizard?** | v2a: länk/banner; v2b: varning på träff om avvikelse på datum/tur |
| 5 | **Realtime/försening?** | **Nej** — utanför scope (UL-app-funktion vi inte har data för) |
| 6 | **Operatörsflöde** | v2a: publik lista först; admin-översikt v2b |

---

## 8. Föreslagen fasindelning (ej påbörjad)

| Fas | Innehåll | Beror på |
|-----|----------|----------|
| **0** | Detta dokument + Jesper-validering av §5–7 | — |
| **1** | Domän/API: «disruption feed» — slå ihop generella + tur-avvikelser, datumintervall, sortering, gruppering per dag/vecka | Beslut #2–3 |
| **2** | Publik Vue-vy: UL-lik lista (rubrik, datum, berörda tåg, brödtext) — ersätter/utökar flat `MrtTrafficNoticesView` | Fas 1 |
| **3** | Shortcode-parametrar, ev. dedikerad sida, docs | Fas 2 |
| **4** | Wizard: banner eller träff-varning | Beslut #4 |
| **5** | Admin: samlad översikt «kommande störningar» | Fas 1–2 |

**Medvetet utanför v2:** GPS/spårning, push, automatisk Trafikverket-feed, Gutenberg-block (samma som v1).

---

## 9. Relation till övriga docs

| Doc | Roll |
|-----|------|
| [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md) | v1 — implementerad bas |
| [TODO.md](TODO.md) | J11/D16 — spåras här tills beslut + fas 0 klara |
| [ADMIN_UX_ACTION_PLAN.md](ADMIN_UX_ACTION_PLAN.md) UX-2.7 | Trafikmeddelanden vs avvikelser — förklaring redan i admin |
| [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) | J11 original |

---

## 10. Nästa steg

- [ ] Jesper/operatör: bekräfta tolkning §5 (särskilt tidshorisont och wizard)
- [ ] Spika D16-defaults i §7
- [ ] Uppdatera [TODO.md](TODO.md) — flytta J11 från «saknar beslut» till aktiv plan när §7 är OK
- [ ] Fas 1 — domän/API (efter beslut)
