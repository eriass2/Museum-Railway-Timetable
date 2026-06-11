# Diskussioner att lösa — Jesper beta (juni 2026)

**Datum:** 2026-06-09  
**Syfte:** Punkter som kräver **beslut, avstämning eller prioritering** innan (eller under) implementation — inte rena kodbuggar.

**Relaterat:**

- [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md) — all feedback
- [2026-06-09-jesper-buggar-plan.md](2026-06-09-jesper-buggar-plan.md) — bekräftade buggar (B1–B3)
- [ADMIN_UX_ACTION_PLAN.md](../ADMIN_UX_ACTION_PLAN.md) — genomförd admin UX-plan (fas 1–5)

**Statusnycklar**

| Symbol | Betydelse |
|--------|-----------|
| 🔴 | Blockerar större leverans — behöver beslut snart |
| 🟡 | Bör diskuteras — flera rimliga alternativ |
| 🟢 | Lätt att besluta — väntar bara på formulering/OK |

---

## Sammanfattning

| Område | Antal | Typisk fråga |
|--------|-------|--------------|
| Reseplanerare — copy & begrepp | 2 | Säger vi ”resbar” eller ”tillgänglig trafik”? |
| Reseplanerare — design | 3 | Fullbredd, bakgrundsbild, font 700 |
| Reseplanerare — navigation | 1 | Hur långt tillbaka i wizarden ska man kunna hoppa? |
| Admin — datamodell & flöde | 6 | Rutter, grid, publicera, zoner |
| Behovsuppehåll (X/P/A) | 1 | Gemensam modell admin ↔ reseplanerare |
| Buss/tåg-koppling | 1 | Manuell karta vs automatisk matchning |
| Lansering & scope | 2 | Beta under säsongen, trafikstörningar |
| Drift & data | 1 | Vem matar in Linnés Hammarby — process |

---

## 1. Reseplanerare — copy och begrepp

### D1. ”Bokningsbar” vs ”resbar” vs ”trafik” 🟡

| | |
|---|---|
| **Feedback** | J7 — *”Inga bokningsbara dagar…”* känns fel; man bokar inte tåg i planeraren |
| **Fråga** | Vilken terminologi ska gälla konsekvent i kalender, legend och tomma tillstånd? |
| **Alternativ** | A) *Resbar* / *Inga resbara dagar* · B) *Trafik för din resa* · C) *Tillgänglig trafik* |
| **Påverkan** | `inc/assets/frontend.php`, `WizardDateStep.vue`, ev. månadskalender |
| **Beslut av** | Produkt/kommunikation (Jesper?) |
| **Status** | **Beslutad** — B) *Ingen trafik för din resa…* / *Trafik för din resa* |

**Förslag till diskussion:** Välj en term och uppdatera både gul legend (*”Kan … för din resa”*) och tomma meddelanden i samma anda.

---

### D2. Beta-etikett och förväntningar 🟢

| | |
|---|---|
| **Feedback** | J13 — provlansera som beta under säsongen |
| **Fråga** | Ska reseplaneraren märkas ”Beta” i UI? Var (sidhuvud, steg 1, footer)? Ska felrapportering länkas? |
| **Alternativ** | A) Banner + kort text · B) Bara i WordPress-sidans titel · C) Ingen märkning, bara intern test-URL |
| **Beslut av** | Produkt / Lennakatten |
| **Status** | **Beslutad** — A) banner i planeraren; admin-inställning `wizard_beta_enabled` |

---

### D2b. Feedback-widget (FAB) 🟢

| | |
|---|---|
| **Feedback** | J13 — samla in buggar och förslag under betatest |
| **Fråga** | Hur ska feedback samlas in? Oberoende av beta-banner? E-post? GDPR? |
| **Beslut av** | Produkt / team (2026-06-10) |
| **Status** | **Beslutad** — se [WIZARD_FEEDBACK_SKETCH.md](../WIZARD_FEEDBACK_SKETCH.md) |

| Beslut | |
|--------|---|
| Feedback vs beta | Oberoende admin-toggles |
| E-post | Valfritt |
| GDPR-text | Ja, i panelen |
| E-postnotis till team | v2 (framtida utveckling) |

---

## 2. Reseplanerare — design

### D3. Desktop-layout — full bredd 🟡

| | |
|---|---|
| **Feedback** | J10 — planeraren ska täcka vänster–höger kant |
| **Fråga** | Ska vita paneler gå **edge-to-edge** (100 vw) eller maxbredd med grön/motiv i marginalerna? |
| **Alternativ** | A) Paneler 100 % bredd inom hero · B) Max ~72rem centrerat · C) Som idag men bredare (~58→72rem) |
| **Påverkan** | `journey-wizard/base.css`, `responsive.css`, inbäddat läge (`embedded`) |
| **Beslut av** | Design / Jesper (grafisk profil) |
| **Status** | **Beslutad** — A) hero full bredd desktop; valfri bakgrundsbild via settings/mediaväljare (J10) |

---

### D4. Bakgrundsbild istället för flat grönt 🔴

| | |
|---|---|
| **Feedback** | J10 — skalbar bakgrundsbild, UL-lik |
| **Fråga** | Vilket motiv? Ska det bytas per säsong? Hur ska det bete sig inbäddat i WordPress-tema? |
| **Behov** | Bildasset (upplösning, beskärning mobil/desktop), ev. `background-size: cover` + overlay för läsbarhet |
| **Alternativ** | A) En fast profilbild · B) CSS gradient + subtil textur · C) Behåll flat grön tills asset finns |
| **Beslut av** | Design — **blockerar** J10-implementation |
| **Status** | **Beslutad** — A) valfri profilbild via admin/settings; flat grön fallback |

---

### D5. Font-weight Open Sans 700 🟢

| | |
|---|---|
| **Feedback** | J9 — ska vara Bold 700, inte 800 |
| **Fråga** | Ska **all** wizard-typografi (rubriker, knappar, tidslinje) vara 700, eller bara brödtext? |
| **Anteckning** | Tekniskt enkel CSS-ändring; diskussionen är scope (admin? tidtabellsöversikt också?) |
| **Beslut av** | Design — kan defaulta till ”hela publika Vue-UI” |
| **Status** | **Beslutad** — A) hela publika Vue-UI, 700 |

---

## 3. Reseplanerare — navigation och UX

### D6. Klickbara steg i progress-baren 🟡

| | |
|---|---|
| **Feedback** | J8 — gula/klara steg ska vara klickbara tillbaka |
| **Fråga** | Vilka steg ska gå att hoppa till? Ska framtida steg vara låsta? Vad händer med valt datum/tur vid hopp? |
| **Regler att bestämma** | • Tillbaka till *route* — rensa allt? · Till *date* — behåll from/to? · Till *outbound* — behåll datum? · *Return* — bara vid tur/retur |
| **Påverkan** | Wizard store, `MrtStepProgress`, tillgänglighet (fokus, aria) |
| **Beslut av** | UX + utveckling |
| **Status** | **Beslutad** — klara steg klickbara tillbaka; framtida steg låsta (J8) |

---

### D7. Kompakt fordonsrad (bara ikoner) 🟢

| | |
|---|---|
| **Feedback** | J1 — ikoner på en rad i grått fält, spara plats |
| **Fråga** | Alltid ikoner only, eller bara mobil? Ska tooltip/aria innehålla full fordonsnamn? |
| **Beslut av** | UX — låg risk att implementera med aria-labels |
| **Status** | **Beslutad** — C) ikoner only i resekort, text i detaljvy |

---

## 4. Admin — datamodell och arbetsflöde

### D8. Rutt: en eller två rutter per linje? 🟡

| | |
|---|---|
| **Feedback** | A0 — Jesper skapade rutt per riktning; upplevdes omständigt |
| **Fråga** | Ska systemet stödja **en rutt med stationer i ordning** och automatisk riktning, eller är dagens modell (rutt + start/slut för riktning) korrekt domänmodell? |
| **Konsekvens** | Påverkar A1 (auto start/slut), import/CSV, tidtabellsgrid riktning |
| **Beslut av** | Domän + operatör — **påverkar hela admin-roadmap** |
| **Status** | Öppen |

---

### D9. Auto start/slutstation (A1) 🟢

| | |
|---|---|
| **Feedback** | Slutstation = sista angivna stationen |
| **Fråga** | Om operatören ** medvetet** vill att start ≠ första station (sällsynt?) — behövs override eller räcker first/last alltid? |
| **Anteckning** | Idag används start/slut för riktning (`MRT_calculate_direction_from_end_station`) |
| **Beslut av** | Operatör / domän |
| **Status** | **Beslutad** — A) auto first/last, ingen override |

---

### D10. Grid som primär inmatning (A2) 🔴

| | |
|---|---|
| **Feedback** | Samtrafiken-lik vy — turer som kolumner, ANK/AVG inline |
| **Fråga** | Ersätter gridet **helt** tur-formuläret, eller kompletterar det? Var skapas tågnummer, trafikdagar, tågtyp? |
| **Alternativ** | A) Skapa minimal tur → fyll allt i grid · B) Behåll två vägar · C) Ny sida ”Trafikdataportal” separat från nuvarande editor |
| **Insats** | Stor — **strategiskt admin-beslut** |
| **Beslut av** | Produkt + utveckling |
| **Beslut** | **A** delvis — egen flik Turvy som primär stopptidsinmatning; turformulär kvar för rutt/tågtyp/tågnummer |
| **Status** | **Beslutad** (fas 2 levererad) |

---

### D11. Publicera-knapp / utkast (A9) 🔴

| | |
|---|---|
| **Feedback** | Förbered tider utan att publik ser dem |
| **Fråga** | Utkast på **tidtabell**-nivå, **tur**-nivå, eller hela plugin-installationen? Vad ska döljas: reseplanerare, månadskalender, tidtabells-PDF, REST? |
| **Alternativ** | A) WP `draft` på `mrt_timetable` · B) Meta `mrt_published` · C) Miljö (staging vs prod) |
| **Konsekvens** | All publik REST + shortcodes måste filtrera |
| **Beslut av** | Produkt + drift |
| **Status** | Öppen |

---

### D12. Zon-etiketter A/B/C (A8) 🟢

| | |
|---|---|
| **Feedback** | Visa A, B, C i stället för 1, 2, 3 |
| **Fråga** | Enbart admin/priser, eller också reseplanerarens prisblock? Vad med zon 4 om den finns i matrisen? |
| **Anteckning** | Lagring kan vara numerisk; etikett är presentationslager |
| **Beslut av** | Produkt / prissättning |
| **Status** | **Beslutad** — C) A/B/C (+ D) i admin och reseplanerare |

---

### D13. Auto slutstation från stopptider (A7) 🟢

| | |
|---|---|
| **Feedback** | Sista station med tid = turens slutstation |
| **Fråga** | Gäller **sista stopp med tid**, eller sista där tåget stannar (`pickup`/`dropoff`)? Hur hanteras turer som vänder före ruttslut? |
| **Beslut** | **Sista station med tid** (ankomst eller avgång) i ruttsordning; manuellt destinationsfält borttaget i turformulär |
| **Beslut av** | Operatör + domän |
| **Status** | **Beslutad** — A) härled vid sparande av stopptider (`MRT_sync_service_end_station_from_stops`) |

---

## 5. Behovsuppehåll — gemensam modell (A3, A4, J4)

### D14. X / P / A — inmatning och visning 🔴

| | |
|---|---|
| **Feedback** | X före tid → behovsuppehåll → ”Ca” + fotnot i reseplanerare; P/A för enkelriktat |
| **Frågor att lösa** | |
| 1 | Ska operatören skriva **textprefix i cellen** (X 10.13) eller använda **kryssrutor** som idag? |
| 2 | Skiljer vi **hållplats** (interpolerad tid) från **behovsuppehåll** (X) i datamodellen? |
| 3 | Exakt fotnot-text — Jespers formuleringar OK som standard? |
| 4 | Gäller Ca bara i **detaljvy**, eller även i tidtabells-PDF och översikt? |
| **Befintligt** | PHP: P/A/X i `MRT_stop_time_prefix_and_time_parts`; admin-grid: dialog + `StopTimePaCheckbox` |
| **Beslut** | **A)** kryssrutor På/Av (befintlig modell); båda utan tid = X; båda med tid = Ca i detaljvy; fotnoter enligt Jespers text |
| **Beslut av** | Operatör + UX + utveckling |
| **Status** | **Beslutad** (fas 3 v1) |

**Fotnoter (Jespers förslag — bekräfta):**

- Påstigande: *”Behovsuppehåll, ge ett tecken till föraren om du vill stiga på”*
- Avstigande: *”Behovsuppehåll, säg till konduktören i god tid om du vill stiga av”*

---

## 6. Buss/tåg-koppling (A10, del av A0)

### D15. Hur ska anslutningar modelleras? 🟡

| | |
|---|---|
| **Feedback** | Buss mot Linnés Hammarby vs Fjällnora ska inte bli godtycklig |
| **Fråga** | Primär sanning: **tidtabellens tider** (auto-match), **train_change_map** (manuell), eller **ny explicit kopplingstabell** (tåg X → buss Y per riktning)? |
| **Utredning** | Se [buggplan B2](2026-06-09-jesper-buggar-plan.md#b2-busståg-koppling-visas-godtyckligt-a10) — kan vara bugg, data eller fel modell |
| **Alternativ** | A) Förbättra auto + bättre admin-visning · B) Kräv manuell karta · C) Koppling per gren i rutten |
| **Beslut av** | Operatör + utveckling — efter B2-utredning |
| **Status** | **Delvis löst (2026-06-10)** — A) levererad: `paired_branches`, bussrader per tågkolumn vid knutpunkt (`8c8a30a`), kolumnsammanslagning vid tågbyte via `train_change_map` (`9a44dda`). Manuell validering Selknä kvar. Explicit kopplingstabell (C) ej aktuell i v1. |

---

## 7. Trafikstörningar och avvikelser (J11)

### D16. UL-lik störningslista 🟡

| | |
|---|---|
| **Feedback** | Lista nuvarande/kommande störningar; ange berörda tåg |
| **Fråga** | Utöka **trafikmeddelanden** (`TrafficNoticesPage`), **tur-avvikelser** i editorn, eller ny sida? Ska störningar synas i reseplaneraren automatiskt? |
| **Befintligt** | Avvikelser per tur/datum; publika trafikmeddelanden — begreppen överlappar (se admin-audit) |
| **Beslut av** | Produkt — scope utanför Jesper-buggfix |
| **Status** | Parkerad |

---

## 8. Drift, data och process

### D17. Linnés Hammarby — data vs verktyg 🟢

| | |
|---|---|
| **Feedback** | J6, A0 — rutter/turer saknades; Jesper matade in manuellt |
| **Fråga** | Är målet **CSV-import**, bättre **onboarding-checklista**, eller **operatörsmanual**? Ska vi leverera färdig fixture för Linnés Hammarby i repo? |
| **Beslut av** | Operatör / projektledning |
| **Status** | **Beslutad** — C) checklista + fixture + CSV-importväg |

---

## 9. Prioritering — vad gör vi först efter buggarna?

### D18. Roadmap efter B1–B3 🟡

| | |
|---|---|
| **Fråga** | Efter bytestid-fix (B1): admin Fas 1 (A1, A7, A8) eller reseplanerare quick wins (J7, J9, J1–J3)? |
| **Förslag** | Parallellt: **J7+J9** (copy + font, liten) + **admin A1** (auto start/slut) medan **B2** utreds |
| **Beslut av** | Team |
| **Status** | **Beslutad** — A) avsluta admin Fas 1: **A7** (→ ev. A6), **J2** polish |
| **Läge juni 2026** | B1–B3, J7/J9/J1, A1, A8, D2/D17 redan levererat i `experiment/vue-public-ui` |

---

## Mötesagenda (förslag, ~45 min)

1. **Copy** — D1, D2 (10 min)
2. **Design** — D4, D3, D5 (10 min)
3. **Admin strategi** — D8, D10, D11 (15 min)
4. **Behovsuppehåll** — D14 (10 min)
5. **Prioritering** — D18, D15 efter B2 (5 min)

---

## Beslutslogg

| Datum | ID | Beslut | Ansvarig |
|-------|-----|--------|----------|
| 2026-06-09 | D5 | **A** — `font-weight: 700` i hela publika Vue-UI (planerare, kalender, tidtabell, priser) | Team |
| 2026-06-09 | D7 | **C** — ikoner only i resekortets grå fält; full text i detaljvy; aria-label + title | Team |
| 2026-06-09 | D9 | **A** — start/slut härleds från första/sista i `station_ids`; dropdowns borttagna | Team |
| 2026-06-09 | D12 | **C** — etiketter A–D i admin + publikt; numerisk fallback över D | Team |
| 2026-06-09 | D2 | **A** — beta-banner ovanför steg-nav; valfri `beta_feedback_url` | Team |
| 2026-06-09 | D17 | **C** — fixture B5/B9–B14, [LINNES_HAMMARBY.md](../LINNES_HAMMARBY.md), admin-checklista | Team |
| 2026-06-09 | D1 | **B** — *Ingen trafik för din resa denna månad* / legend *Trafik för din resa* | Team |
| 2026-06-09 | D18 | **A** — nästa: **A7** (auto slutstation), ev. A6; **J2** som CSS-polish; J8/A2 senare | Team |
| 2026-06-09 | D13 | **A** — sista station med tid i ruttsordning; synkas vid stopptidssparande; dropdown borttagen | Team |
| 2026-06-09 | D18 | **A6** — en angiven tid speglas till ankomst och avgång vid stopptidssparande | Team |
| 2026-06-09 | D10 | **A** delvis — flik Turvy primär; A5 tom cell = ingen stopp | Team |
| 2026-06-09 | D14 | **A** — P/A-kryssrutor; Ca/X i wizard-detalj; Jespers fotnoter | Team |
| 2026-06-10 | D3 | **A** — hero full bredd desktop (J10) | Team |
| 2026-06-10 | D4 | **A** — valfri bakgrundsbild via settings/mediaväljare; grön fallback | Team |
| 2026-06-10 | D6 | Klara wizard-steg klickbara tillbaka; framtida steg låsta (J8) | Team |
| 2026-06-10 | D2b | Feedback-widget v1 — FAB, CPT, admin-lista; e-postnotis v2 | Team |
| 2026-06-10 | D15 | **A** delvis — bussrader per tågkolumn + kolumnsammanslagning; manuell Selknä-check kvar | Team |

*(Fyll i rad när punkt är avgjord; länka ev. PR/commit.)*

---

## Nästa steg

- [ ] Boka avstämning med Jesper / operatör kring D8, D11 (D1, D3, D4, D6, D14 avgjorda)
- [x] Efter B2-utredning: uppdatera D15 med konkret rekommendation (2026-06-10)
- [x] Uppdatera status i [2026-06-09-jesper-beta.md](2026-06-09-jesper-beta.md) — 2026-06-11
