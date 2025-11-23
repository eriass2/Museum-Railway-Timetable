# Route Integration Plan

## Problem Identifierat

**Nuvarande modell:**
- Service → Stop Times → Stations
- Problem: En tidtabell innehåller flera avgångar i olika riktningar på en sträcka
- Problem: Tidtabell kan gälla olika dagar
- Problem: Ingen koppling mellan services som går samma sträcka

**Föreslagen modell:**
- Route → Services → Stop Times → Stations
- Route = Grundläggande sträcka (t.ex. "Hultsfred - Västervik")
- Route fungerar i båda riktningar
- Services = Avgångar på sträckan (kan välja vilka stations de stannar vid)
- Calendar = När services körs

## Ny Datamodell

### Route (Grundläggande Element)
- **Titel**: Sträckans namn (t.ex. "Hultsfred - Västervik")
- **Stations**: Lista över alla stations på sträckan (i ordning)
- **Båda riktningar**: Sträckan fungerar fram och tillbaka

### Service (Avgång på Sträcka)
- **Titel**: Service-namn (t.ex. "Vardagstidtabell Nordgående")
- **Route**: Kopplad till en Route
- **Direction**: Nordgående/Sydgående (eller A→B / B→A)
- **Stop Times**: Vilka stations på sträckan service stannar vid
- **Calendar**: När service körs

### Stop Times (Vilka Stations Service Stannar Vid)
- **Service**: Kopplad till Service
- **Station**: Välj från stations på Route
- **Sequence**: Ordning längs sträckan
- **Arrival/Departure**: Tider
- **Pickup/Dropoff**: Om passagerare kan stiga på/av

## Implementation Plan

### Steg 1: Route Meta Box
- Lägg till meta box i Route edit-sidan
- Lista över stations på sträckan (i ordning)
- Lägg till/ta bort stations från sträckan
- Spara som post meta eller custom table

### Steg 2: Service koppling till Route
- Lägg till Route-dropdown i Service meta box
- När Route väljs, visa stations på sträckan
- Service kan välja vilka stations den stannar vid

### Steg 3: Stop Times UI Uppdatering
- När Route är vald, visa endast stations på sträckan
- Automatisk sequence baserat på Route-ordning
- Möjlighet att välja vilka stations service stannar vid

### Steg 4: Database Schema
**Alternativ A: Post Meta**
- Route: `mrt_route_stations` (serialized array av station IDs)
- Service: `mrt_service_route_id` (route post ID)

**Alternativ B: Custom Table**
- Ny tabell: `mrt_route_stations`
  - `route_post_id`
  - `station_post_id`
  - `sequence`
  - `direction` (optional: 'forward' eller 'backward')

**Rekommendation**: Alternativ B (Custom Table) för bättre query-prestanda

### Steg 5: Backward Compatibility
- Befintliga Services utan Route ska fortfarande fungera
- Migration script för att konvertera befintliga data

## Arbetsflöde Efter Integration

### 1. Skapa Route
- Railway Timetable → Routes → Add New
- Lägg till stations i ordning (t.ex. Hultsfred → Västervik → Oskarshamn)

### 2. Skapa Service
- Railway Timetable → Services → Add New
- Välj Route
- Välj Direction (Nordgående/Sydgående)
- Välj vilka stations service stannar vid
- Lägg till tider för varje station
- Lägg till Calendar (när service körs)

### 3. Fördelar
- Enklare att skapa flera services på samma sträcka
- Automatisk sequence-ordning
- Bättre översikt över vilka services går samma sträcka
- Enklare att ändra sträckan (uppdatera Route, alla services uppdateras)

## Ytterligare Förbättringar

### Hjälptext/Placeholders
- Lägg till placeholders i alla input-fält
- Lägg till description-text under fält
- Exempel: "T.ex. Hultsfred - Västervik" för Route-namn

### Rensa DB-knapp
- Lägg till knapp i admin för att rensa alla data
- Endast för utveckling (kräver capability check)
- Rensa: Stations, Services, Routes, Stop Times, Calendar
- Varning: "Detta kommer radera ALL data. Är du säker?"

