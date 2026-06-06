# Admin arbetsflöde – Vue-admin

Steg-för-steg i **Vue-admin** under **Tidtabell** i WordPress (`admin.php?page=mrt_app`). Hash-routing: `#/dashboard`, `#/timetables/123`, osv. Dev-verktyg: `#/dev-tools` (endast dev-läge).

**Navigation:** på desktop används WordPress vänstermeny (Översikt → Stationer & rutter → Tidtabeller → …). På smal skärm visas även flikar i appen.

## List ↔ detalj

De flesta entitetslistor visar **en vy i taget**:

- **Lista** – översikt med knappar för redigera / lägg till
- **Detalj** – formulär för en post; **Tillbaka till listan** återgår till listan
- Osparade ändringar i detaljvyn → bekräftelsedialog innan tillbaka (turer, rutter, tågtyper, stopptider m.m.)

Undantag: **stationer** redigeras fortfarande inline i tabellen. **Inställningar** och **priser** är enkla formulär/spreadsheet utan list↔detalj.

---

## Översikt

För en fungerande tidtabell behöver du:

1. **Stationer** – var tågen stannar
2. **Rutter** – sträckor med stationer i ordning
3. **Tågtyper** (valfritt) – kategorisering och ikoner
4. **Tidtabeller** – vilka dagar tidtabellen gäller
5. **Turer** – tåg kopplade till tidtabell och rutt
6. **Stopptider** – tider och hållplatser per tur

---

## Steg 1: Stationer

**Meny:** Railway Timetable → **Stationer & rutter** (`#/stations-routes`) → fliken **Stationer**

1. Skriv stationsnamn i fältet **Ny station** och klicka **Lägg till**
2. Redigera i tabellen: **Namn**, **Typ**, **Lat/Lng** (valfritt, för karta), **Buss** (suffix i visning), **Ordning**
3. Klicka **Spara** på raden

---

## Steg 2: Rutter

**Samma sida** – fliken **Rutter**

1. Klicka **Ny rutt** i listan
2. Ange namn och valfria stationer/ändpunkter → **Lägg till**
3. För befintlig rutt: **Redigera** → ändra namn och stationer → **Spara rutt**
4. **Tillbaka till listan** avbryter utan att lämna sidan (bekräftelse om du ändrat något)

Ruttordningen styr vilka stationer som erbjuds vid stopptider.

---

## Steg 3: Tågtyper (valfritt)

**Meny:** **Tågtyper** (`#/train-types`)

1. Klicka **Skapa tågtyp** (listan)
2. Fyll i namn, slug (valfritt) och ikon → **Skapa tågtyp**
3. Befintlig typ: **Redigera** → **Spara** eller **Tillbaka till listan**

---

## Steg 4: Tidtabell

**Meny:** **Tidtabeller** (`#/timetables`)

1. Klicka **Ny tidtabell** → ange namn och typ → **Skapa**
2. I editorn: **Titel** och **Typ (färg i översikt)** → **Spara namn och typ**
3. Fliken **Trafikdagar**: lägg till datum → **Spara**

---

## Steg 5: Turer

**Fliken Turer** i tidtabellseditorn

1. Klicka **Lägg till tur** (listan)
2. Välj **Rutt**, **Tågtyp** (valfritt) och **Destination** → **Lägg till tur**
3. Befintlig tur: **Redigera** → **Spara tur** eller **Tillbaka till listan**
4. **Stopptider** på en rad hoppar till stopptidsfliken för den turen

---

## Steg 6: Stopptider

**Fliken Stopptider**

1. Listan visar alla turer – klicka **Stopptider** på en rad
2. Redigera tider och hållplatser → **Spara stopptider**
3. **Tillbaka till listan** (bekräftelse om osparade tider)

**Rutnät** (hopfällbart under editorn): klicka i celler för att ändra tid, stannar och P/A (passagerare på/av).

**Fliken Förhandsvisning** visar samma översikt som på webbplatsen (read-only).

---

## Avvikelser och drift

**Fliken Avvikelser** (desktop) eller mobilpanelen i editorn:

1. Klicka **Lägg till avvikelse** eller **Redigera** på en rad
2. Välj datum och tur (vid ny), **tågtyp**, **Inställt tåg** och **Meddelande**
3. **Spara** i detaljvyn uppdaterar listan (minne); **Spara avvikelser** skickar till servern

Inställda turer **visas kvar** i tidtabell och reseplanerare med tydlig markering (genomstrukna tider, badge «Inställd»). Reseplaneraren låter inte välja en inställd tur.

**Förhandsvisning** (fliken i editorn) visar samma avvikelse- och inställ-markering som på webbplatsen.

### Mobil drift

På **Översikt** (`#/dashboard`) när det finns trafik idag:

- **Inställ trafik idag** – sätter meddelandet «Inställd» på alla dagens turer
- **Öppna tidtabell** / **Ändra avgångstid** – går till editorn

I mobil editor:

- **Snabb avgångstid** – ändra avgång vid första hållplats
- **Inställ trafik** – samma som ovan, om dagens datum finns i tidtabellen

---

## Inställningar, priser, import

| Sida | Route | Behörighet |
|------|-------|------------|
| Inställningar | `#/settings` | `manage_options` |
| Priser | `#/prices` | `manage_options` |
| Import/export | `#/import-export` | `manage_options` |

Redaktörer (`edit_posts`) ser dashboard, tidtabeller och stationer, och kan **inte** ändra grunddata – bara avvikelser och snabb avgångstid.

### Radera

| Entitet | Var | Villkor |
|---------|-----|---------|
| Tidtabell | Tidtabellslista / editor | Raderar alla turer och stopptider |
| Tur | Editor → Turer | — |
| Station | Stationer & rutter | Blockeras om station finns i rutt, tur eller stopptider |
| Rutt | Stationer & rutter | Blockeras om turer använder rutten |
| Tågtyp | Tågtyper | — |

**Hjälp:** `#/help` — arbetsflöde och vanliga frågor i admin.

---

## REST API

All klient–server-kommunikation går via WordPress REST (`/wp-json/museum-railway-timetable/v1/…`). Se [REST_API.md](REST_API.md).

---

## Felsökning

| Problem | Kontroll |
|---------|----------|
| Varningar på dashboard | Följ länkarna under **Varningar** |
| Tur utan tider | Stopptider-fliken; kontrollera att rutten har stationer |
| Ingen trafik idag | Lägg till dagens datum under **Trafikdagar** |
| Legacy CPT-URL | Redirectar automatiskt till Vue-admin |
