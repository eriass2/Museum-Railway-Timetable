# Trafikstörningar v2 — UL-referens och Jesper J11

**Status:** beslutad riktning (2026-06-11) — webb only, två källor; implementation ej påbörjad  
**Källa:** [2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) J11, [2026-06-09-jesper-diskussioner.md](feedback/2026-06-09-jesper-diskussioner.md) D16  
**Föregångare (v1, klar):** [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md) — shortcode + generella meddelanden + dagens tur-avvikelser

---

## 1. Syfte

Jesper efterfrågar en **UL-lik** trafikstörningsvy. Detta dokument beskriver:

1. **Vad UL gör** (referens för operatör och utveckling)
2. **Vad Lennakatten-pluginet redan har** (v1)
3. **Tolkning** — vad J11 sannolikt betyder i vår kontext
4. **Gap** v1 → v2 (§6)
5. **Beslutad riktning** — webb only, två källor (§5–7)

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

### 2.2 UL-appen / reseplanerare (referens — **ej vår scope**)

| UL-element | Hur det fungerar |
|------------|------------------|
| **Störning vid sökresultat** | Trafikstörningar visas **tillsammans med reseförslag** — inte bara på en separat infosida. |
| **Favoriter** | Sparade resor visar om det finns störning **på just den resan**. |
| **Tidsuppdatering** | Vid försening: gammal tid överstruken, ny tid visas (realtime). |

Jesper nämner **UL-lik lista** på **webben** (J11). Vi tar **inte** med reseplanerare, realtime eller push — se **beslut §5**.

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

**Styrkor v1:** Turkoppling finns i datamodellen; operatör kan redan mata in störningar; API slår redan ihop **två källor** (`MRT_traffic_notices_aggregate`).  
**Svagheter vs UL:** Ingen samlad «aktuellt»-feed med **kommande** veckor; ingen tydlig **gruppering av flera tåg**; flat punktlista istället för UL-layout; max **2 dagar** i shortcode; admin har **två inmatningsställen** (meddelanden vs avvikelser) utan gemensam översikt.

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

## 5. Beslutad riktning (2026-06-11)

### 5.0 Scope — webb only

| Beslut | |
|--------|---|
| **Publik vy** | UL-lik **feed på webben** (shortcode / ev. egen sida) — som UL «Aktuellt» |
| **Reseplaneraren** | **Nej** — ingen banner, ingen träff-varning, ingen realtime |
| **Realtime / karta / push** | **Nej** — utanför scope |

### 5.1 Datamodell — två källor, en publik lista

v2 bygger på **befintlig data**. Ingen ny CPT «störning» i v2a — bara bättre **presentation och gruppering** i API/feed.

```
Operatör: Tur-avvikelse (Tidtabell → Avvikelser)  ──┐
Operatör: Trafikmeddelande (#/traffic-notices)     ──┼──► disruption feed ──► UL-lik webb-lista
System: grupperar, sorterar, datumintervall          ──┘
```

| Källa | Inmatning (oförändrad) | Publik feed |
|-------|------------------------|-------------|
| **A — Tur-avvikelser** | Tidtabell → **Avvikelser** (per tur + datum: inställt, ersättningstyp, text) | **Auto-genererade** rader — t.ex. *Inställd — Tåg 71*, *Ersättningsbuss — Tåg 73* |
| **B — Systemmeddelanden** | **Trafikmeddelanden** (`#/traffic-notices`) — fri text, `active_from`/`active_to`, sortering | **Manuella** rader — glassrea, baninfo, «all trafik …» |

**Sammanfogning:** samma princip som v1 (`aggregate.php`), utökad med:

- längre **tidshorisont** (standard **90 dagar**, konfigurerbart)
- **UL-lik layout** (datum, rubrik, berörda tåg)
- **gruppering** — flera turer med samma avvikelsetext/datum → *«Tåg 71, 73, 96»* i en rad

**Inmatning ändras inte** — operatör fortsätter mata avvikelser respektive meddelanden där de gör idag.

### 5.2 Publik «trafikinfo»-feed (UL Aktuellt)

En sektion på startsidan (shortcode) eller egen undersida:

- **Lista** sorterad på datum — **Pågår nu** | **Kommande**
- Varje post:
  - **Datum/intervall** (dag eller `från–till`)
  - **Rubrik** med **berörda tågnummer** (71, 96, B3 …)
  - **Kort brödtext** (från avvikelse-notice eller meddelande-text)
  - Ev. typ: *Inställt* / *Ersättning* / *Info*

Exempel (målbild):

```
15 okt 2026
  Inställd trafik — Tåg 71, 73, 96
  Tåg inställda pga banarbete …

1 jul – 16 aug 2026
  Baninfo — Tåg B3, B4
  Buss ersätter vid Selknä …
```

### 5.3 Berörda tåg

- **Från avvikelser (A):** tågnummer från `mrt_service_number` + route; gruppera rader med samma notice + datum(intervall)
- **Från meddelanden (B):** operatören skriver tågnummer i texten vid behov (som UL skriver linjenummer i rubriken)

### 5.4 Admin — inmatning och översikt

- **Inmatning:** oförändrad (Avvikelser + Trafikmeddelanden)
- **Översikt (önskad):** samma feed som publik vy, med länk till redigering — **fas 4** (valfritt blocker för fas 1–2)

---

## 6. Gap: v1 → beslutad v2

| UL / Jesper | v1 idag | v2 (beslut) |
|-------------|---------|-------------|
| Aktuellt-feed med datum i rubrik | Punktlista, ingen rubrik/datum per rad för avvikelser | UL-lik feed-komponent |
| Kommande veckor/månader | Max 2 dagar | **90 dagar** (`horizon_days`) |
| Flera linjer/tåg per störning | En tur per avvikelse-rad | **Gruppering** i API (källa A) |
| Planerade banarbeten (intervall) | Källa B med `active_from`/`active_to` **eller** många tur-datum i A | Källa B + grupperad A |
| Störning i reseplanerare | Saknas | **Medvetet nej** (§5.0) |
| En admin-översikt | Två inmatningsmenyer | Förhandsvisning av feed (fas 4) |

---

## 7. Produktbeslut (D16)

| # | Fråga | Beslut (2026-06-11) |
|---|--------|------------------------|
| 1 | **Var publiceras?** | **Webb only** — shortcode (startsidan) + ev. egen WP-sida «Trafikstörningar» |
| 2 | **Reseplaneraren?** | **Nej** — ingen integration i wizard |
| 3 | **Tidshorisont?** | **90 dagar** (konfigurerbart shortcode-param) — nu + kommande |
| 4 | **Datamodell?** | **Två befintliga källor** (§5.1): auto från tur-avvikelser + manuella trafikmeddelanden; gruppering i API, ingen ny CPT i v2a |
| 5 | **Realtime/försening?** | **Nej** |
| 6 | **Operatörsflöde inmatning?** | **Oförändrat** — Avvikelser + Trafikmeddelanden |
| 7 | **Admin-översikt?** | **Önskad** — samma feed som publik (**fas 4**); inte blocker för fas 1–2 |

**Kvar att validera med Jesper:** målbild §5.2 (layout/rubriker) och 90-dagars horisont räcker för «kommande».

---

## 8. Föreslagen fasindelning

| Fas | Innehåll | Status |
|-----|----------|--------|
| **0** | Plan + beslut §5–7 | ☑ 2026-06-11 |
| **1** | Domän/API: **disruption feed** — källor A+B, 90-dagars fönster, gruppering tågnummer, sortering Pågår/Kommande | Ej påbörjad |
| **2** | Publik Vue: UL-lik feed — ersätter/utökar `MrtTrafficNoticesView` | Efter fas 1 |
| **3** | Shortcode `horizon_days`, docs, ev. dedikerad sida | Efter fas 2 |
| **4** | Admin: förhandsvisning av samma feed + länkar till redigering | Efter fas 2 |

**Medvetet utanför v2:** reseplanerare, GPS/realtime, push, ny CPT, Gutenberg-block, Trafikverket-feed.

**Nyckelfiler (troliga):** `inc/domain/traffic-notices/aggregate.php` (utöka), ny `disruption-feed.php`, `MrtTrafficNoticesView.vue` / ny feed-komponent, `TrafficNoticesDomainTest.php`.

---

## 9. Relation till övriga docs

| Doc | Roll |
|-----|------|
| [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md) | v1 — implementerad bas (källor A+B, flat lista) |
| [TODO.md](TODO.md) | J11/D16 — aktiv plan, fas 1 nästa |
| [ADMIN_UX_ACTION_PLAN.md](ADMIN_UX_ACTION_PLAN.md) UX-2.7 | Trafikmeddelanden vs avvikelser — förklaring i admin |
| [feedback/2026-06-09-jesper-beta.md](feedback/2026-06-09-jesper-beta.md) | J11 original |

---

## 10. Nästa steg

- [x] Spika riktning: webb only, två källor (§5) — 2026-06-11
- [ ] Jesper: snabb OK på målbild §5.2 och 90 dagar
- [ ] Fas 1 — disruption feed (API/domän)
- [ ] Fas 2 — UL-lik Vue-feed på webben
