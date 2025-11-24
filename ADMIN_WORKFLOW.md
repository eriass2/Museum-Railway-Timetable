# Admin Arbetsflöde - Skapa en Tidtabell

Detta dokument beskriver det rekommenderade arbetsflödet för att skapa en komplett tidtabell i admin-gränssnittet.

## Översikt

För att skapa en fungerande tidtabell behöver du:

1. **Stations** - Var tågen stannar
2. **Routes** - Definiera sträckor med stations i ordning
3. **Train Types** (valfritt) - Kategorisera tåg (t.ex. ånglok, diesellok)
4. **Services** - Vilka tåg/tidtabeller som finns (kopplade till Routes)
5. **Stop Times** - Vilka stationer varje service stannar vid och när
6. **Calendar** - När varje service körs (datumintervall och veckodagar)

---

## Steg-för-steg Guide

### Steg 1: Skapa Stations

**Varför först?** Stations behövs innan du kan skapa Stop Times.

**Så här gör du:**

1. Gå till **Railway Timetable → Stations** i admin-menyn
2. Klicka på **"Add New"** (eller "Lägg till ny")
3. Fyll i:
   - **Titel**: Stationens namn (t.ex. "Hultsfred Museum")
   - **Station Type**: Välj typ (Station, Halt, Depot, eller Museum)
   - **Latitude/Longitude**: (valfritt) Koordinater för kartvisning
   - **Display Order**: Ordning för sortering (lägre nummer = högre upp)
4. Klicka **"Publish"** (eller "Publicera")

**Tips:**
- Du kan skapa alla stations på en gång, eller skapa dem när du behöver dem
- **Display Order** används för att sortera stations i listor och dropdowns

---

### Steg 2: Skapa Routes

**Varför?** Routes definierar sträckor med stations i ordning. När du skapar en Service kan du välja en Route, och då visas alla stations på sträckan automatiskt så att du enkelt kan välja vilka stations tåget stannar vid.

**Så här gör du:**

1. Gå till **Railway Timetable → Routes** i admin-menyn
2. Klicka på **"Add New"**
3. Fyll i:
   - **Titel**: Route-namnet (t.ex. "Hultsfred - Västervik", "Main Line")
   - Hjälptext visas direkt under title-fältet med exempel
4. I **"Route Stations"** meta box:
   - Välj en station från dropdown
   - Klicka **"Add Station to Route"**
   - Upprepa för varje station i ordning (första stationen först, sista sist)
5. Klicka **"Publish"** (eller "Update")

**Tips:**
- Skapa en Route för varje unik sträcka (t.ex. "Nordgående", "Sydgående", "Huvudlinje")
- Stations ordning i Route är viktig - den används när du konfigurerar Stop Times
- Du kan ha flera Routes med samma stations men i olika ordning
- Exempel: "Hultsfred → Västervik" och "Västervik → Hultsfred" kan vara två olika Routes

---

### Steg 3: Skapa Train Types (Valfritt men Rekommenderat)

**Varför?** Train Types låter dig kategorisera services (t.ex. "Ånglok", "Diesellok", "Elektrisk") och filtrera i shortcodes.

**Så här gör du:**

1. Gå till **Railway Timetable → Train Types**
2. Klicka på **"Add New Train Type"**
3. Fyll i:
   - **Name**: T.ex. "Ånglok", "Diesellok", "Elektrisk"
   - **Slug**: Skapas automatiskt från namnet (t.ex. "steam", "diesel")
4. Klicka **"Add New Train Type"**

**Tips:**
- Du kan lägga till Train Types när som helst
- Services kan ha flera Train Types
- Train Types används för filtrering i shortcodes

---

### Steg 4: Skapa Services

**Varför?** Services är själva tidtabellerna. Varje service representerar ett tåg med specifika tider.

**Så här gör du:**

1. Gå till **Railway Timetable → Services**
2. Klicka på **"Add New"**
3. Fyll i:
   - **Titel**: Service-namnet (t.ex. "Vardagstidtabell 09:00", "Helgtidtabell 10:00")
   - **Route**: **VÄLJ EN ROUTE** (obligatoriskt) - Välj den Route som denna service kör på
   - **Train Type**: Välj tågtyp (t.ex. "Ånglok", "Diesellok")
   - **Direction**: (valfritt) Riktning (t.ex. "Nordgående", "Sydgående")
4. Klicka **"Publish"** (eller "Update")

**Tips:**
- **Route är obligatoriskt** - Du måste välja en Route innan du kan konfigurera Stop Times
- Skapa en service för varje unik avgång (t.ex. "Vardagstidtabell 09:00", "Vardagstidtabell 11:00", "Helgtidtabell 10:00")
- Du kan skapa många services med olika tider på samma Route (t.ex. 12 avgångar per riktning)
- Exempel: Om du har Route "Hultsfred → Västervik" kan du skapa flera services med olika avgångstider

---

### Steg 5: Konfigurera Stop Times för varje Service

**Vad är Stop Times?** Detta definierar vilka stationer varje service stannar vid, i vilken ordning, och när (ankomst/avgångstider).

**Så här gör du:**

1. Gå till **Railway Timetable → Services**
2. Öppna en service för redigering (klicka på titeln)
3. **VIKTIGT**: Kontrollera att du har valt en **Route** i "Service Details" meta box. Om inte, välj en Route och klicka "Update".
4. Scrolla ner till **"Stop Times"** meta box
5. Du ser nu alla stations på Route:n i en tabell:
   - **Order**: Stationsordning på sträckan (1, 2, 3...)
   - **Station**: Stationsnamn
   - **Stops here**: Checkbox - kryssa i för varje station där tåget stannar
   - **Arrival**: Ankomsttid (HH:MM) - lämna tomt för första stationen
   - **Departure**: Avgångstid (HH:MM) - lämna tomt för sista stationen
   - **Pickup/Dropoff**: Kryssa i om passagerare kan gå på/av
6. För varje station där tåget stannar:
   - Kryssa i **"Stops here"**
   - Fyll i **Arrival** och/eller **Departure** tider
   - Välj **Pickup** och/eller **Dropoff** om tillämpligt
7. Klicka **"Save Stop Times"** längst ner för att spara alla ändringar

**Tips:**
- **Route måste väljas först** - Om ingen Route är vald visas ett meddelande
- När du kryssar i "Stops here" aktiveras tidsfälten automatiskt
- Första stationen behöver oftast ingen ankomsttid (lämna Arrival tomt)
- Sista stationen behöver oftast ingen avgångstid (lämna Departure tomt)
- Du kan välja att tåget ska köra förbi vissa stations (lämna "Stops here" avkryssat)
- Alla ändringar sparas på en gång när du klickar "Save Stop Times"

**Exempel:**
```
Order | Station          | Stops | Arrival | Departure | Pickup | Dropoff
------|------------------|-------|---------|-----------|--------|--------
1     | Hultsfred Museum | ✓     | —       | 10:00     | ✓      | ✓
2     | Västervik        | ✓     | 10:30   | 10:35     | ✓      | ✓
3     | Oskarshamn       | ✓     | 11:00   | —         | ✓      | ✓
4     | Kalmar           | —     | —       | —         | —      | —
```

I exemplet ovan stannar tåget vid de tre första stations men kör förbi Kalmar.

---

### Steg 6: Lägg till Calendar Entries för varje Service

**Vad är Calendar?** Detta definierar **när** varje service körs (datumintervall, veckodagar, och specialdagar).

**Så här gör du:**

1. I samma Service edit-sida, scrolla ner till **"Calendar (Service Schedule)"** meta box
2. I tabellen längst ner (den grå "Add New"-raden):
   - Ange **Start Date** (YYYY-MM-DD)
   - Ange **End Date** (YYYY-MM-DD)
   - Kryssa i veckodagar när servicen körs (Mån, Tis, Ons, etc.)
   - (Valfritt) **Include Dates**: Kommaseparerade datum att inkludera (överrider veckodagar)
   - (Valfritt) **Exclude Dates**: Kommaseparerade datum att exkludera
3. Klicka **"Add"**
4. Upprepa för varje datumintervall

**Tips:**
- **Include Dates** används för specialdagar (t.ex. "2025-07-04" för 4 juli)
- **Exclude Dates** används för att hoppa över dagar (t.ex. julafton)
- Du kan ha flera calendar entries per service (t.ex. sommar och vinter)
- Du kan klicka på en rad för att redigera den direkt

**Exempel för "Vardagstidtabell":**
```
Date Range        | Days              | Include | Exclude
------------------|-------------------|---------|--------
2025-06-01 to     | Mån, Tis, Ons,    | —       | —
2025-08-31        | Tor, Fre          |         |
```

**Exempel för "Specialdag 4 juli":**
```
Date Range        | Days | Include    | Exclude
------------------|------|------------|--------
2025-07-04 to     | —    | 2025-07-04 | —
2025-07-04        |      |            |
```

---

## Komplett Exempel: Skapa en Säsongstidtabell

Låt oss säga att du vill skapa en säsongstidtabell för juni–augusti med:
- Vardagstidtabell (måndag–fredag)
- Helgtidtabell (lördag–söndag)
- Specialtidtabell för 4 juli

### Steg 1: Skapa Stations
1. Skapa "Hultsfred Museum"
2. Skapa "Västervik"
3. Skapa "Oskarshamn"

### Steg 2: Skapa Route
1. Skapa Route "Hultsfred → Västervik"
2. Lägg till stations i ordning: Hultsfred Museum, Västervik, Oskarshamn

### Steg 3: Skapa Train Types
1. Skapa "Ånglok" (slug: "steam")
2. Skapa "Diesellok" (slug: "diesel")

### Steg 4: Skapa Services
1. Skapa "Vardagstidtabell 09:00" (Route: "Hultsfred → Västervik", Train Type: Ånglok, Direction: Nordgående)
2. Skapa "Helgtidtabell 10:00" (Route: "Hultsfred → Västervik", Train Type: Ånglok, Direction: Nordgående)
3. Skapa "Specialdag 4 juli 14:00" (Route: "Hultsfred → Västervik", Train Type: Ånglok, Direction: Nordgående)

### Steg 5: Konfigurera Stop Times

**För "Vardagstidtabell 09:00":**
- Kryssa i "Stops here" för alla tre stations
- Hultsfred Museum: Departure 09:00
- Västervik: Arrival 09:30, Departure 09:35
- Oskarshamn: Arrival 10:00
- Klicka "Save Stop Times"

**För "Helgtidtabell 10:00":**
- Kryssa i "Stops here" för alla tre stations
- Hultsfred Museum: Departure 10:00
- Västervik: Arrival 10:30, Departure 10:35
- Oskarshamn: Arrival 11:00
- Klicka "Save Stop Times"

**För "Specialdag 4 juli 14:00":**
- Kryssa i "Stops here" för alla tre stations
- Hultsfred Museum: Departure 14:00
- Västervik: Arrival 14:30, Departure 14:35
- Oskarshamn: Arrival 15:00
- Klicka "Save Stop Times"

### Steg 6: Lägg till Calendar

**För "Vardagstidtabell 09:00":**
- Date Range: 2025-06-01 to 2025-08-31
- Days: Mån, Tis, Ons, Tor, Fre

**För "Helgtidtabell 10:00":**
- Date Range: 2025-06-01 to 2025-08-31
- Days: Lör, Sön

**För "Specialdag 4 juli 14:00":**
- Date Range: 2025-07-04 to 2025-07-04
- Days: (inga)
- Include Dates: 2025-07-04

---

## Tips och Best Practices

1. **Börja med Stations** - De behövs för allt annat
2. **Skapa Routes först** - Routes definierar sträckor och gör det enklare att konfigurera Services
3. **Använd Display Order** - Sortera stations logiskt (1, 2, 3...) i Stations
4. **Tydliga Service-namn** - T.ex. "Vardagstidtabell 09:00" istället för "Service 1"
5. **Välj Route innan Stop Times** - Route måste väljas för att konfigurera Stop Times
6. **Använd "Stops here" checkbox** - Enkelt att välja vilka stations tåget stannar vid
7. **Spara Stop Times på en gång** - Klicka "Save Stop Times" när du är klar med alla ändringar
8. **Testa Calendar-logik** - Se till att datumintervall och veckodagar är korrekta
9. **Använd Train Types** - Gör det enklare att filtrera i shortcodes
10. **Spara ofta** - Klicka "Update" efter varje större ändring
11. **Skapa många Services** - Du kan skapa många services (t.ex. 12 avgångar) med olika tider på samma Route

---

## Felsökning

**Problem: Service visas inte i shortcode**
- Kontrollera att Calendar entries är korrekta (datumintervall, veckodagar)
- Kontrollera att Stop Times finns för servicen

**Problem: Fel stationer visas**
- Kontrollera att rätt Route är vald för servicen
- Kontrollera att "Stops here" är ikryssat för rätt stations
- Kontrollera stations ordning i Route:n

**Problem: Tider visas inte**
- Kontrollera att Arrival/Departure-tider är korrekta (HH:MM-format)
- Första stationen behöver ingen Arrival, sista ingen Departure

**Problem: Service körs på fel dagar**
- Kontrollera Calendar entries (veckodagar, include/exclude dates)
- Kontrollera datumintervall (start_date ≤ end_date)

---

## Nästa Steg

När du har skapat din tidtabell kan du:

1. **Visa den på frontend** - Använd shortcodes (se README.md)
2. **Kontrollera Stations Overview** - Se översikt över alla stations
3. **Testa shortcodes** - Verifiera att allt fungerar korrekt

