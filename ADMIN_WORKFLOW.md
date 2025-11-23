# Admin Arbetsflöde - Skapa en Tidtabell

Detta dokument beskriver det rekommenderade arbetsflödet för att skapa en komplett tidtabell i admin-gränssnittet.

## Översikt

För att skapa en fungerande tidtabell behöver du:

1. **Stations** - Var tågen stannar
2. **Train Types** (valfritt) - Kategorisera tåg (t.ex. ånglok, diesellok)
3. **Services** - Vilka tåg/tidtabeller som finns
4. **Stop Times** - Vilka stationer varje service stannar vid och när
5. **Calendar** - När varje service körs (datumintervall och veckodagar)

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
- Du kan också importera stations via CSV (se Steg 6)

---

### Steg 2: Skapa Train Types (Valfritt men Rekommenderat)

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

### Steg 3: Skapa Services

**Varför?** Services är själva tidtabellerna. Varje service representerar ett tåg med specifika tider.

**Så här gör du:**

1. Gå till **Railway Timetable → Services**
2. Klicka på **"Add New"**
3. Fyll i:
   - **Titel**: Service-namnet (t.ex. "Vardagstidtabell", "Helgtidtabell", "Ånglokstur")
   - **Direction**: (valfritt) Riktning (t.ex. "Nordgående", "Sydgående")
4. Välj **Train Types** (om du skapat några)
5. Klicka **"Publish"**

**Tips:**
- Skapa en service för varje unik tidtabell
- Exempel: "Vardagstidtabell", "Helgtidtabell", "Specialdag 4 juli"
- Du kan skapa flera services nu och konfigurera dem senare

---

### Steg 4: Lägg till Stop Times för varje Service

**Vad är Stop Times?** Detta definierar vilka stationer varje service stannar vid, i vilken ordning, och när (ankomst/avgångstider).

**Så här gör du:**

1. Gå till **Railway Timetable → Services**
2. Öppna en service för redigering (klicka på titeln)
3. Scrolla ner till **"Stop Times"** meta box
4. I tabellen längst ner (den grå "Add New"-raden):
   - Välj **Station** från dropdown
   - Ange **Sequence** (ordning: 1, 2, 3...)
   - Ange **Arrival Time** (HH:MM, eller lämna tomt för första stationen)
   - Ange **Departure Time** (HH:MM, eller lämna tomt för sista stationen)
   - Kryssa i **Pickup** och/eller **Dropoff** om tillämpligt
5. Klicka **"Add"**
6. Upprepa för varje station i ordning

**Tips:**
- **Sequence** måste vara unik per service (1, 2, 3, 4...)
- Första stationen behöver oftast ingen ankomsttid
- Sista stationen behöver oftast ingen avgångstid
- Du kan klicka på en rad för att redigera den direkt
- Du kan ta bort en rad genom att klicka "Delete"

**Exempel:**
```
Sequence | Station          | Arrival | Departure
---------|------------------|---------|----------
1        | Hultsfred Museum | —       | 10:00
2        | Västervik        | 10:30   | 10:35
3        | Oskarshamn       | 11:00   | —
```

---

### Steg 5: Lägg till Calendar Entries för varje Service

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
1. Skapa "Hultsfred Museum" (Sequence: 1)
2. Skapa "Västervik" (Sequence: 2)
3. Skapa "Oskarshamn" (Sequence: 3)

### Steg 2: Skapa Train Types
1. Skapa "Ånglok" (slug: "steam")
2. Skapa "Diesellok" (slug: "diesel")

### Steg 3: Skapa Services
1. Skapa "Vardagstidtabell" (Train Type: Ånglok, Direction: Nordgående)
2. Skapa "Helgtidtabell" (Train Type: Ånglok, Direction: Nordgående)
3. Skapa "Specialdag 4 juli" (Train Type: Ånglok, Direction: Nordgående)

### Steg 4: Lägg till Stop Times

**För "Vardagstidtabell":**
- Hultsfred Museum (Seq: 1, Departure: 09:00)
- Västervik (Seq: 2, Arrival: 09:30, Departure: 09:35)
- Oskarshamn (Seq: 3, Arrival: 10:00)

**För "Helgtidtabell":**
- Hultsfred Museum (Seq: 1, Departure: 10:00)
- Västervik (Seq: 2, Arrival: 10:30, Departure: 10:35)
- Oskarshamn (Seq: 3, Arrival: 11:00)

**För "Specialdag 4 juli":**
- Hultsfred Museum (Seq: 1, Departure: 14:00)
- Västervik (Seq: 2, Arrival: 14:30, Departure: 14:35)
- Oskarshamn (Seq: 3, Arrival: 15:00)

### Steg 5: Lägg till Calendar

**För "Vardagstidtabell":**
- Date Range: 2025-06-01 to 2025-08-31
- Days: Mån, Tis, Ons, Tor, Fre

**För "Helgtidtabell":**
- Date Range: 2025-06-01 to 2025-08-31
- Days: Lör, Sön

**För "Specialdag 4 juli":**
- Date Range: 2025-07-04 to 2025-07-04
- Days: (inga)
- Include Dates: 2025-07-04

---

## Alternativ: CSV Import (för Bulk Data)

Om du har mycket data kan du importera via CSV istället:

1. Gå till **Railway Timetable → CSV Import**
2. Välj tab (Stations, Stop Times, eller Calendar)
3. Ladda ner sample CSV för att se formatet
4. Skapa din CSV-fil
5. Klistra in och klicka **"Import"**

**När använda CSV:**
- Du har många stations att skapa
- Du har många services med många stop times
- Du importerar data från ett annat system

**När använda Admin UI:**
- Du skapar/redigerar några få items
- Du vill se och kontrollera data direkt
- Du gör små ändringar

---

## Tips och Best Practices

1. **Börja med Stations** - De behövs för allt annat
2. **Använd Display Order** - Sortera stations logiskt (1, 2, 3...)
3. **Tydliga Service-namn** - T.ex. "Vardagstidtabell" istället för "Service 1"
4. **Kontrollera Sequence** - Stop Times måste vara i rätt ordning (1, 2, 3...)
5. **Testa Calendar-logik** - Se till att datumintervall och veckodagar är korrekta
6. **Använd Train Types** - Gör det enklare att filtrera i shortcodes
7. **Spara ofta** - Klicka "Update" efter varje större ändring

---

## Felsökning

**Problem: Service visas inte i shortcode**
- Kontrollera att Calendar entries är korrekta (datumintervall, veckodagar)
- Kontrollera att Stop Times finns för servicen

**Problem: Fel stationer visas**
- Kontrollera Sequence-ordningen i Stop Times
- Kontrollera att rätt stations är valda

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

