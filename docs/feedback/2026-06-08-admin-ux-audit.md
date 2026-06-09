# Admin UX-granskning — Vue SPA

**Datum:** 2026-06-08  
**Omfattning:** Alla vyer i Vue-admin (`admin.php?page=mrt_app`, hash-routing `#/…`)  
**Metod:** Kodgenomgång av komponenter, sidor och mönster; jämförelse med `docs/ADMIN_WORKFLOW.md` och feedback-dokument. Ingen live-användartest i denna omgång.  
**Åtgärdsplan:** [ADMIN_UX_ACTION_PLAN.md](../ADMIN_UX_ACTION_PLAN.md)

**Statusnycklar**

| Symbol | Betydelse |
|--------|-----------|
| ✅ | Troligen OK — snabb visuell koll räcker |
| 🔍 | Bör inspekteras manuellt (medel risk) |
| ⚠️ | Sannolikt UX-problem — prioritera granskning och ev. fix |

---

## Sammanfattning

Admin-appen har **konsekvent grundmönster** (lista ↔ detalj, tillbaka-navigering, osparade ändringar, l10n). De **största UX-riskerna** ligger i:

1. **Domänterminologi** — särskilt *Destination* vs *Slutstation*
2. **Dubbla redigeringsvägar** — stopptider (tabell vs tidtabellsgrid)
3. **Olik sparmodell** — direktspara vs batch (avvikelser, trafikdagar)
4. **Mobil vs desktop** — tidtabellsredigeraren är inte bara responsiv utan **olika komponenter**
5. **Priser** — hög komplexitet, svår onboarding utan dokumentation

**Turformuläret** (skapa/redigera tur) har nyligen förbättrats med etiketter och fältordning; **resten av admin följer inte samma formmönster** (`mrt-admin-trip-fields`).

---

## Korsvisande teman

### Terminologi

| Term | Var den syns | Problem |
|------|--------------|---------|
| **Destination** | Turer (lista + formulär), stopptidslista, mobil | Ruttnamn har redan ändpunkter (t.ex. Faringe – Uppsala Östra); fältet är *turens slutstation längs rutten* |
| **Avvikelse** vs **Trafikmeddelande** | Tidtabell-flik vs egen meny | Begreppen överlappar för slutanvändare; docs förklarar men UI knyter inte ihop dem |
| **P/A** (pickup/dropoff) | Stopptider | Förkortning i kolumnheader med tooltip — ok för erfarna, svårt för nya |

**Rekommendation:** Byt etikett *Destination → Slutstation* överallt i admin. Lägg kort förklaring på trafikmeddelanden-sidan om skillnad mot tur-avvikelser.

### Formulärmönster (inkonsistens)

| Mönster | Används i |
|---------|-----------|
| `mrt-admin-trip-fields` (stackad, max-width, label ovanför) | Tur skapa/redigera |
| `<p>` + label / `mrt-admin-deviation-detail` | Avvikelser skapa/redigera |
| `StationEditorFields` (label ovanför) | Station skapa/redigera |
| `form-table` (WP-klassisk) | Inställningar |
| Horisontell `AdminInlineForm` / `mrt-admin-trip-form` | Trafikdagar, mobil avvikelser, import |

**Rekommendation:** Extrahera gemensam `AdminLabeledFields`-layout och applicera på avvikelser, trafikmeddelanden och ev. meta-panel.

### Sparflöden

| Vy | Sparbeteende |
|----|--------------|
| Tur, station, rutt, tågtyp, meta | Direkt vid knapp |
| Trafikdagar | Lägg till i lista → **Spara** hela listan |
| Avvikelser | Lägg till/redigera utkast → **Spara avvikelser** (batch) |
| Stopptider | Per tur, direkt |
| Priser | Hela formuläret |
| Trafikmeddelanden | Per meddelande |

**Rekommendation:** `AdminUnsavedBanner` finns redan på flera ställen — bra. Överväg kort hjälptext vid batch-spar ("Ändringar sparas inte förrän du klickar …") på avvikelser och trafikdagar.

### Roller (`can_manage` vs `can_operate`)

- **Operate** ser tidtabell, avvikelser, trafikmeddelanden, stopptider (begränsat) men inte stationer/priser/import.
- Begränsad roll-meddelande finns på dashboard och tidtabellslista.
- **Testa båda rollerna** vid manuell granskning.

### Mobil (`isMobile`)

På smal skärm:

- Tidtabellsredigeraren **döljer desktop-flikar** (turer, stopptider, avvikelser, preview).
- `MobileTimetablePanel` visar snabb avgång, ställ in trafik, avvikelser.
- Trafikdagar visas **ovanför** mobilpanelen (bra).
- Tidtabellslista: skapa inline på mobil, separat vy på desktop.

**Rekommendation:** Verifiera att operatörer som bara använder telefon förstår att turer/stopptider/redigering kräver desktop (eller dokumentera tydligt).

---

## Navigation och skal

### Sidomeny (`AdminNav.vue`) — ✅

**Styrkor:** Tydlig hierarki; trafikmeddelanden synliga för operate; dev-tools dold utanför dev-läge.

**Att kolla:** På mobil — dubbel navigation (WP-meny + ev. app)? `ADMIN_WORKFLOW.md` nämner flikar på smal skärm; verifiera att det inte blir rörigt.

### App-shell (`AdminApp.vue`) — ✅

Minimal layout: nav + `RouterView` + bekräftelsedialog. Inga UX-problem förväntade.

---

## 1. Översikt (`/dashboard`) — 🔍

**Komponenter:** `DashboardPage`, `SetupChecklist`, `TrafficTodayPanel`

### Styrkor

- Setup-checklist med progress och direktlänkar — bra onboarding.
- Varningar med klickbara länkar till rätt vy.
- Nästa trafik-tabell.
- Snabbstart-knappar.

### Problem / frågor

- Statistik som löpande text (desktop) är kompakt men lite anonym — ingen visuell tyngd.
- Checklistan försvinner när allt är klart — ok, men ny operatör ser den bara en gång.
- `TrafficTodayPanel` — oklar koppling till mobil "ställ in trafik" (samma data?).

### Rekommendationer

- Manuell koll: klicka igenom checklist-steg som ny användare.
- Ev. behåll en hopfällbar "Kom igång" även efter complete.

---

## 2. Stationer & rutter (`/stations-routes`) — 🔍

**Underflikar:** Stationer | Rutter  
**Vyer per flik:** lista | skapa | redigera

### 2a. Stationer — lista — ✅

**Styrkor:** Filter "visa bara utan priszon", gul markering, länk till hjälp om priszoner.

### 2b. Station — skapa/redigera (`StationEditorFields`) — 🔍

**Styrkor:** Etiketter på alla fält; tågbyte i disclosure; priszoner som checkboxar.

**Problem:**

- Priszoner kräver domänkunskap — hjälptext finns i listvy men inte i formuläret.
- Koordinater (lat/lng) utan validering/format-hint i UI (placeholder finns).
- `display_order` syns i lista men **inte** i editorn (sortering oklar).

**Rekommendationer:**

- Kort hint under priszoner i formuläret + länk till `#/help?section=price-zones`.
- Överväg att dölja koordinater i disclosure om de sällan används.

### 2c. Rutter — lista — ✅

**Styrkor:** `RoutePreview` compact i tabell — visuellt bra.

### 2d. Rutt — skapa/redigera (`RouteStationOrderEditor`) — ⚠️

**Styrkor:** Tydlig sektion för start/slut; numrerad stationslista; badges för start/slut längs listan; flytta ↑↓.

**Problem:**

- **Skapa rutt:** stationsordning ligger i **disclosure** ("Fler fält") — lätt att skapa rutt utan stationer.
- Start/slut-väljare listar bara stationer **redan i ordningslistan** — korrekt logik men kan förvirra innan man lagt till stationer.
- Flytt-knappar med ↑↓/× — funktionellt men lite kryptiskt utan tooltips (aria-label finns).

**Rekommendationer:**

- Vid skapa: visa minst "lägg till första station" utanför disclosure, eller validera och varna om tom `station_ids`.
- Manuell test: skapa rutt Faringe–Uppsala med mellanstationer.

---

## 3. Tidtabeller — lista (`/timetables`) — ✅

**Vyer:** lista | skapa (desktop)

### Styrkor

- Tydlig tabell med antal datum/turer.
- Skapa-vy med titel + typ (färg) — etiketter på desktop.
- Mobil: kortlista + inline skapa.

### Problem

- Mobil skapa-formulär: titel **utan synlig label** (bara placeholder).
- Tidtabellstyp (grön/gul/röd/orange) — ingen hint om att den styr **kalenderfärg** publikt.

**Rekommendation:** Hjälptext vid typ-fält (samma som meta-panel).

---

## 4. Tidtabell — redigerare (`/timetables/:id`) — ⚠️

**Desktop-flikar:** Trafikdagar | Turer | Stopptider | Avvikelser | Förhandsvisning  
**Mobil:** Meta + trafikdagar + `MobileTimetablePanel` (ersätter flikarna)

### 4a. Meta (`TimetableEditorMetaPanel`) — 🔍

**Styrkor:** Titel + typ + spara/radera; unsaved banner.

**Problem:**

- Typ-fält utan förklaring (kalenderfärg).
- Radera tidtabell bredvid spara — farlig placering (confirm-dialog finns troligen).

### 4b. Trafikdagar (`TimetableEditorDatesTab`) — 🔍

**Styrkor:** Enkel datumlista; ta bort per rad.

**Problem:**

- Inline-form: datum + lägg till + spara i **samma rad** — kan klippas på smal desktop.
- Batch-spar — inte uppenbart att "lägg till" inte sparar direkt (banner hjälper).

### 4c. Turer — lista — 🔍

**Styrkor:** Tydlig tabell; åtgärder Redigera / Stopptider / Ta bort.

**Problem:**

- Kolumn **Destination** (terminologi).
- Många knappar per rad — trångt på smal skärm.

### 4d. Turer — skapa/redigera — 🔍 (nyligen förbättrad)

**Styrkor:** Stackat formulär; fältordning; destination disabled utan rutt; hjälptexter; highlight i disclosure.

**Kvar att fixa:**

- Etikett *Destination → Slutstation*.
- Ev. visa ruttprefix (Faringe → … → Uppsala) när rutt vald.

### 4e. Stopptider — lista + detalj — ⚠️

**Komponenter:** `TimetableEditorStoptimesPanel`, `StopTimesEditor`

**Styrkor:**

- Lista → detalj med tillbaka + trip-label.
- Tabell med tid-input per station.
- P/A-kolumner med `abbr` + tooltip i header.

**Problem:**

- **Tät tabell** — 6 kolumner; P/A bara som förkortning i cell (checkbox utan label).
- `stops_here`-checkbox utan tydlig koppling till ankomst/avgång.
- **Grid-redigering** (`EditableTimetableOverview`) ligger i `<details>` — lätt att missa; annat mental model än tabellen.
- Grid: klick på cell → dialog (`OverviewGridCellEditor`) — bra dialog, men **upptäckbarhet** låg.
- Grid och tabell synkar via REST men användaren ser inte att de är samma data.

**Rekommendationer (hög prioritet):**

1. Manuell test: redigera samma tid i tabell vs grid — verifiera konsekvens.
2. Överväg P/A-labels i tabell (som i grid-dialogen) eller legend under tabellen.
3. Gör grid-sektionen mer synlig (ej collapsed by default?) eller lägg länk "Redigera i tidtabellsvy".
4. Hint: "Stopptider sparas per tur — öppna en tur i listan ovan."

### 4f. Avvikelser — desktop (`TimetableEditorDeviationsTab`) — ⚠️

**Styrkor:** Lista med datum/tur/typ/inställd/meddelande; unsaved banner; skapa/redigera med labels.

**Problem:**

- **Batch-spar** — lägg till flera avvikelser, spara en gång (ok för power users, oklart för andra).
- Skapa-flöde: separat detaljvy (bra) men **inte** samma visuella form som turer.
- Lista-vy: inga inline-filter/sök vid många avvikelser.
- "Inställt tåg"-checkbox sätter notice-text automatiskt — meddelandefältet kan fortfarande redigeras (förvirrande?).

**Rekommendationer:**

- Harmonisera detaljformulär med `mrt-admin-trip-fields`.
- Kort introtext: "Tur-avvikelser visas i trafikmeddelanden-shortcoden tillsammans med generella meddelanden."

### 4g. Förhandsvisning (flik Preview) — ✅

Read-only publik tidtabell. Bra för WYSIWYG-koll.

**Att kolla:** Matchar färger/layout det besökare ser (J1-feedback)?

---

## 5. Mobil tidtabell (`MobileTimetablePanel`) — ⚠️

**Sektioner:** Snabb avgång | Ställ in trafik idag | Avvikelser

### Styrkor

- Fokuserat på **dagsdrift** (avgång, inställa trafik).
- Avvikelser som kort med `AdminDeviationRowFields`.
- Breda knappar (`wide`).

### Problem

- **Ingen** tur-/stopptids-redigering på mobil — stor funktionell lucka om operatör bara har telefon.
- Lägg till avvikelse: **horisontell rad utan labels** (samma anti-mönster som gamla turformuläret).
- Avvikelser redigeras inline i kort — desktop använder lista + detalj (inkonsistent).
- Snabb avgång uppdaterar bara **första stationens avgång** — kraftfullt men riskabelt utan tydlig varning.

**Rekommendationer:**

- Labels på avvikelse-selects (som E2E nu gör för turer).
- Tydlig varning på snabb avgång: "Ändrar endast avgång från [station]."
- Dokumentera i hjälp: "Full redigering kräver desktop."

---

## 6. Trafikmeddelanden (`/traffic-notices`) — 🔍

**Vyer:** lista | skapa/redigera (`TrafficNoticesForm`)

### Styrkor

- Tydlig lista med datum, aktiv, ordning (upp/ner).
- Formulär med teckenräknare och synlighets-label.
- Tomt tillstånd.

### Problem

- Formulär: labels **inuti** `<label>` med textarea (ok men inkonsekvent med turformulär).
- Ingen explicit koppling till **tur-avvikelser** i UI.
- Aktiv-kolumn i lista: ✓ vs — (lite sparsamt).
- Ingen förhandsvisning av hur meddelandet ser ut publikt.

**Rekommendationer:**

- Inför kort "Skillnad mot tur-avvikelser"-ruta högst upp.
- Ev. preview-ruta med `MrtAlert`-stil.

---

## 7. Priser (`/prices`) — ⚠️

**Komponenter:** matris, schema (biljettyper/kategorier/zoner), eftermiddags-retur, förhandsvisning, kopiera zon

### Styrkor

- Varning om tom matris.
- `PricesPreview` — operatör ser effekt.
- Schema i disclosures — håller huvudvyn hanterbar.
- Bekräftelse vid radering av typ/kategori/zon.
- Unsaved guard.

### Problem

- **Hög kognitiv belastning** — största admin-vyn.
- Matris: rader = biljettyper × kategorier, kolumner = zoner — kräver förståelse för priszoner på stationer.
- `zone_cap` tekniskt begrepp.
- Eftermiddags-tröskel sparas tillsammans med priser (eller settings) — otydligt att det är två domäner.
- Mobil: responsiva tabeller hjälper men matrisen är troligen opraktisk på telefon.

**Rekommendationer:**

1. Manuell onboarding-test: kan någon fylla i priser med bara Hjälp-sidan?
2. Steg-för-steg-wizard (stationer → zoner → matris) — framtida förbättring.
3. Kort "Börja här"-länk till `#/help?section=price-zones` högst upp.

---

## 8. Inställningar (`/settings`) — 🔍

### Styrkor

- Klassisk WP `form-table` — bekant för WP-administratörer.
- Hints på de flesta fält.
- Eftermiddags-tröskel pekar till priser.

### Problem

- `min_transfer_minutes` / `max_transfer_minutes` **utan** hint (max transfers har hint).
- Tekniska fält (byte-tider) kan skrämma icke-tekniska operatörer.
- Plugin "aktiv/inaktiv" + "note" — oklart syfte för slutanvändare.

**Rekommendation:** Hints på min/max transfer; gruppera "Reseplanerare" vs "Allmänt".

---

## 9. Tågtyper (`/train-types`) — ✅

### Styrkor

- Lista ↔ skapa/redigera med tillbaka.
- `TrainTypeIconPicker` — visuellt tydligt.
- Flash vid sparad rad.
- Dirty guard.

**Att kolla:** Skapa flöde — slug-fält om det exponeras (verifiera i UI).

---

## 10. Import/export (`/import-export`) — 🔍

### Styrkor

- Tydlig steg-för-steg workflow högst upp.
- Import/export/clear i separata paneler.
- Advanced mode (merge/override) i disclosure.
- Omfattande guide i disclosure längst ner.

### Problem

- **Override** farlig — varning finns men lätt att missa i disclosure.
- Fil-input dold — triggas via knapp (ok, men feedback efter val?)
- Clear all — destruktivt; confirm finns i composable (verifiera).

**Rekommendation:** Manuell test med demo-CSV; bekräfta felmeddelanden är begripliga.

---

## 11. Shortcodes (`/shortcodes`) — ✅

### Styrkor

- Tydlig quick-ref-tabell.
- Steg-för-steg montering.
- `AdminShortcodesGuide` med parametrar.

**Att kolla:** Responsiv tabell på mobil.

---

## 12. Hjälp (`/help`) — ✅

### Styrkor

- Strukturerat innehåll från PHP-config.
- Deep link `?section=price-zones`.
- FAQ, workflow, operations.
- Filtrering admin-only/dev-only sektioner.

### Problem

- Lång sida — ingen innehållsförteckning med hopp-länkar (utom price-zones-id).
- Mycket text — operatörer kanske inte läser.

**Rekommendation:** Ev. sticky TOC eller fler deep links från andra vyer (redan delvis: stationer → priszoner).

---

## 13. Dev tools (`/dev-tools`) — ✅

Endast dev-läge. Knapp-lista med confirm på clear. Ingen operatör-UX-granskning nödvändig.

---

## Prioriterad inspektionslista (manuell test)

| Prio | Vy | Varför | Föreslagen åtgärd |
|------|-----|--------|-------------------|
| P0 | Stopptider (tabell + grid) | Dubbel redigeringsväg, tät UI | Test + ev. synligare grid / P/A-labels |
| P0 | Priser | Mest komplex | Onboarding-test med ny användare |
| P1 | Avvikelser (desktop + mobil) | Batch-spar, inkonsistent form | Harmonisera form; labels på mobil |
| P1 | Rutt skapa/redigera | Disclosure döljer stationer | Validering / tydligare tom-state |
| P1 | Terminologi Destination | Förvirring i turer + stopptider | Byt till Slutstation i l10n |
| P2 | Trafikmeddelanden | Överlapp med avvikelser | Förklarande ruta + ev. preview |
| P2 | Mobil tidtabell | Saknar tur/stopptid-redigering | Dokumentera + snabb avgång-varning |
| P2 | Trafikdagar | Batch-spar | Ev. hint vid första besök |
| P2 | Meta / tidtabellstyp | Kalenderfärg okänd | Hint vid typ-fält |
| P3 | Inställningar | Transfer-fält utan hint | Lägg till descriptions |
| P3 | Dashboard | Onboarding försvinner | Ev. permanent hjälp-länk |
| P3 | Import override | Risk i disclosure | Starkare varning / confirm |

---

## Rekommenderad visuell granskningsordning (~45 min)

1. `#/dashboard` — checklist + varningar
2. `#/stations-routes` — skapa station, skapa rutt med 3+ stationer
3. `#/timetables` — skapa tidtabell, lägg trafikdagar
4. `#/timetables/:id` → Turer (skapa + redigera)
5. Samma tidtabell → Stopptider (tabell + öppna grid)
6. Samma tidtabell → Avvikelser (skapa inställd tur, spara batch)
7. `#/traffic-notices` — nytt meddelande
8. `#/prices` — matris + preview
9. `#/settings` — spara
10. `#/import-export` — ladda ner mall
11. Smal skärm / mobil: tidtabell + avvikelser

**Roller:** upprepa steg 4–6 som `can_operate` (utan manage).

---

## Bilaga: vy ↔ komponent

| Route | Huvudkomponent | Subvyer |
|-------|----------------|---------|
| `/dashboard` | `DashboardPage` | checklist, traffic today, stats |
| `/stations-routes` | `StationsRoutesPage` | stations list/create/edit, routes list/create/edit |
| `/timetables` | `TimetableListPage` | list, create |
| `/timetables/:id` | `TimetableEditorPage` | meta, dates, trips, stoptimes, deviations, preview; mobil panel |
| `/traffic-notices` | `TrafficNoticesPage` | list, form |
| `/prices` | `PricesPage` | matrix, schema disclosures, afternoon |
| `/settings` | `SettingsPage` | single form |
| `/train-types` | `TrainTypesPage` | list, create, edit |
| `/import-export` | `ImportExportPage` | import, export, clear, guide |
| `/shortcodes` | `ShortcodesPage` | static guide |
| `/help` | `HelpPage` | static sections |
| `/dev-tools` | `DevToolsPage` | tool buttons |

---

## Nästa steg (implementation)

Om teamet vill gå från rapport till fix, föreslås denna ordning:

1. **L10n:** `editorColDestination` → Slutstation (+ prompt/hint)
2. **Avvikelser:** applicera `mrt-admin-trip-fields` på detaljformulär; labels på mobil add-rad
3. **Stopptider:** legend för P/A; grid `<details open>` eller tydligare CTA
4. **Meta + skapa tidtabell:** hint om tidtabellstyp → kalenderfärg
5. **Trafikmeddelanden:** informationsruta om skillnad mot avvikelser
6. **Inställningar:** hints på transfer-fält

---

*Genererad från kodbasen `frontend/vue/src/admin/` 2026-06-08. Uppdatera efter manuell granskning eller UX-ändringar.*
