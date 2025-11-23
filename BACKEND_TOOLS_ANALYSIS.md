# Backend Tools Analysis

## Nuvarande Verktyg

### ✅ Vad som FINNS:

1. **Services (CPT) - Redigering**
   - Skapa/redigera services via WordPress standard edit-sida
   - Meta box med "Direction" fält
   - Tillgängligt via: Railway Timetable → Services

2. **Stations (CPT) - Redigering**
   - Skapa/redigera stations via WordPress standard edit-sida
   - Meta box med: Station Type, Latitude, Longitude, Display Order
   - Tillgängligt via: Railway Timetable → Stations

3. **CSV Import**
   - Importera Stations, Stop Times, och Calendar via CSV
   - Tre separata tabs för varje typ
   - Sample CSV downloads
   - Tillgängligt via: Railway Timetable → CSV Import

4. **Stations Overview**
   - Översikt över alla stations
   - Visar: Type, Display Order, Services Count, Next Running Day
   - Filter på Train Type
   - Tillgängligt via: Railway Timetable → Stations Overview

5. **Train Types (Taxonomy)**
   - Skapa/redigera train types
   - Tillgängligt via: Railway Timetable → Train Types

---

## ❌ Vad som SAKNAS:

### Problem 1: Ingen visuell hantering av Stop Times

**Nuvarande situation:**
- Stop Times kan BARA importeras via CSV
- Ingen möjlighet att se/redigera stop times för en service direkt i backend
- Ingen översikt över vilka stationer en service stannar vid

**Vad som behövs:**
- Meta box i Service edit-sidan som visar alla stop times för servicen
- Möjlighet att lägga till/redigera/ta bort stop times direkt
- Visuell tabell med: Station, Sequence, Arrival, Departure

### Problem 2: Ingen visuell hantering av Calendar

**Nuvarande situation:**
- Calendar entries kan BARA importeras via CSV
- Ingen möjlighet att se/redigera calendar entries för en service direkt
- Ingen översikt över när en service körs

**Vad som behövs:**
- Meta box i Service edit-sidan som visar alla calendar entries för servicen
- Möjlighet att lägga till/redigera/ta bort calendar entries direkt
- Visuell tabell med: Date Range, Weekdays, Include/Exclude Dates

### Problem 3: Ingen översikt över Service-struktur

**Nuvarande situation:**
- Ingen sida som visar en komplett översikt över en service
- Svårt att se relationen mellan Service → Stop Times → Calendar

**Vad som behövs:**
- En "Service Details" sida eller förbättrad edit-sida som visar:
  - Service info (namn, direction, train types)
  - Alla stop times (med stationer och tider)
  - Alla calendar entries (när servicen körs)

---

## Rekommenderade Förbättringar

### Prioritet 1: Meta Boxes för Stop Times och Calendar

**Lägg till i Service edit-sidan:**

1. **Stop Times Meta Box**
   - Lista alla stop times för servicen
   - Lägg till ny stop time (dropdown för station, fält för sequence, times)
   - Redigera/ta bort befintliga stop times
   - Sortering efter sequence

2. **Calendar Meta Box**
   - Lista alla calendar entries för servicen
   - Lägg till ny calendar entry (datumintervall, veckodagar, include/exclude)
   - Redigera/ta bort befintliga entries
   - Visuell kalender-vy för att se när servicen körs

### Prioritet 2: Service Overview Page

**Ny admin-sida: "Service Details"**
- Dropdown för att välja service
- Visa komplett information:
  - Service info
  - Stop Times (med stationer)
  - Calendar entries (med datumintervall)
  - När servicen körs nästa gång

### Prioritet 3: Förbättrad CSV Import

**Befintlig funktionalitet är bra, men:**
- Lägg till "Export" funktionalitet
- Möjlighet att exportera befintliga data till CSV för backup/redigering
- Export per service eller alla services

---

## Nuvarande Arbetsflöde

### För att skapa en komplett service med 2-3 återanvända tidtabeller:

**Steg 1: Skapa Services**
1. Gå till Railway Timetable → Services → Add New
2. Skapa "Vardagstidtabell" (namn + direction)
3. Skapa "Helgtidtabell" (namn + direction)
4. Skapa "Specialdag 4 juli" (namn + direction)

**Steg 2: Importera Stop Times (via CSV)**
1. Gå till Railway Timetable → CSV Import → Stop Times
2. Skapa CSV med alla stop times för alla services
3. Importera

**Steg 3: Importera Calendar (via CSV)**
1. Gå till Railway Timetable → CSV Import → Calendar
2. Skapa CSV med alla calendar entries för alla services
3. Importera

**Problem:**
- ❌ Ingen visuell feedback om vad som importerades
- ❌ Svårt att se om allt är korrekt kopplat
- ❌ Om man vill ändra en stop time måste man exportera, redigera CSV, och importera igen
- ❌ Ingen översikt över hela strukturen

---

## Slutsats

### ✅ CSV Import fungerar, MEN...

**För att "pussla ihop" 2-3 services med olika stop times och calendar entries:**

**Nuvarande verktyg:**
- ✅ CSV Import (fungerar men är inte visuellt)
- ✅ Service/Station redigering (men saknar stop times/calendar)

**Saknas:**
- ❌ Visuell hantering av Stop Times i Service edit-sidan
- ❌ Visuell hantering av Calendar i Service edit-sidan
- ❌ Översikt över service-struktur

**Rekommendation:**
Lägg till meta boxes för Stop Times och Calendar i Service edit-sidan så att användare kan:
1. Se alla stop times och calendar entries för en service
2. Lägga till/redigera/ta bort direkt i backend
3. Få visuell feedback om strukturen

Detta skulle göra det mycket enklare att "pussla ihop" services utan att behöva använda CSV för varje liten ändring.


