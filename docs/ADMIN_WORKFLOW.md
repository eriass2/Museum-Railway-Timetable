# Admin arbetsflöde – Vue-admin

Steg-för-steg i **Vue-admin** under **Tidtabell** i WordPress (`admin.php?page=mrt_app`). Hash-routing: `#/dashboard`, `#/timetables/123`, osv. Dev-verktyg: `#/dev-tools` (endast dev-läge).

**Navigation:** på desktop används WordPress vänstermeny (Översikt → Stationer & rutter → Tidtabeller → …). På smal skärm visas även flikar i appen.

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

**Meny:** Railway Timetable → **Stationer & rutter** (`#/stations-routes`)

1. Skriv stationsnamn i fältet **Ny station** och klicka **Lägg till**
2. Redigera i tabellen: **Namn**, **Typ**, **Lat/Lng** (valfritt, för karta), **Buss** (suffix i visning), **Ordning**
3. Klicka **Spara** på raden

---

## Steg 2: Rutter

**Samma sida** – panelen **Rutter**

1. Skapa rutt med **Ny rutt** → **Lägg till**
2. Klicka **Redigera** på en rutt
3. Lägg till stationer i ordning med dropdown **Lägg till station…** → **Lägg till**
4. Ordna med **↑** / **↓**, ta bort med **×**
5. **Spara rutt**

Ruttordningen styr vilka stationer som erbjuds vid stopptider.

---

## Steg 3: Tågtyper (valfritt)

**Meny:** **Tågtyper** (`#/train-types`)

1. Fyll i namn, slug (valfritt) och ikon
2. **Lägg till** eller **Spara** på befintlig rad

---

## Steg 4: Tidtabell

**Meny:** **Tidtabeller** (`#/timetables`)

1. Ange namn under **Ny tidtabell** → **Skapa**
2. I editorn: **Titel** och **Typ (färg i översikt)** → **Spara namn och typ**
3. Fliken **Trafikdagar**: lägg till datum → **Spara**

---

## Steg 5: Turer

**Fliken Turer** i tidtabellseditorn

1. Välj **Rutt**, **Tågtyp** (valfritt) och **Destination**
2. **Lägg till tur**

---

## Steg 6: Stopptider

**Fliken Stopptider**

- **Rutnät:** klicka i celler för att ändra tid, stannar och P/A (passagerare på/av)
- **Tabellvy:** välj tur under *Tabellvy för en tur* → redigera → **Spara stopptider**

**Fliken Förhandsvisning** visar samma översikt som på webbplatsen (read-only).

---

## Avvikelser och drift

**Fliken Avvikelser** (desktop) eller mobilpanelen i editorn:

- Byt **tågtyp** eller **meddelande** för ett visst datum och tur
- **Spara avvikelser**

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
