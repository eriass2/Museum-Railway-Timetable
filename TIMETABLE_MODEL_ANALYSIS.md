# Timetable Model Analysis

## Användningsfall

**Scenario:**
- Under en säsong (t.ex. juni–augusti) har ni 2–3 tabeller (services) som återanvänds för de flesta dagarna
- Några dagar har en special tidtabell (helt annan tidtabell)

## Nuvarande Modell

### Hur det fungerar nu:

1. **Service** = En tidtabell (t.ex. "Vardagstidtabell", "Helgtidtabell")
2. **Calendar** = Definierar när en service körs (datumintervall + veckodagar + undantag)
3. **Stop Times** = Definierar vilka stationer och tider för varje service

### Exempel: Säsong juni–augusti

**Service A: "Vardagstidtabell"**
- Calendar: start_date=2025-06-01, end_date=2025-08-31
- Körs: mon=1, tue=1, wed=1, thu=1, fri=1, sat=0, sun=0
- Stop Times: Station 1 (09:00), Station 2 (09:15), Station 3 (09:30)

**Service B: "Helgtidtabell"**
- Calendar: start_date=2025-06-01, end_date=2025-08-31
- Körs: mon=0, tue=0, wed=0, thu=0, fri=0, sat=1, sun=1
- Stop Times: Station 1 (10:00), Station 2 (10:20), Station 3 (10:40)

**Service C: "Specialdag 4 juli"**
- Calendar: start_date=2025-07-04, end_date=2025-07-04
- Körs: mon=0, tue=0, wed=0, thu=0, fri=0, sat=0, sun=0, include_dates=2025-07-04
- Stop Times: Station 1 (14:00), Station 2 (14:30), Station 3 (15:00) - HELT ANNAN TIDTABELL

## Stödjer modellen detta?

### ✅ JA - Modellen stödjer detta!

**För återanvända services (2-3 tabeller):**
- ✅ Varje service har sina egna Stop Times (olika tider för olika services)
- ✅ Calendar definierar när varje service körs (veckodagar)
- ✅ Flera services kan köras på samma dag (t.ex. både Service A och Service B på lördag om båda är aktiva)

**För specialdagar:**
- ✅ Skapa en separat Service med sina egna Stop Times
- ✅ Använd `include_dates` för att köra den bara på specialdagen
- ✅ ELLER: Använd ett kort datumintervall (start_date = end_date = specialdagen)

## Logiken i `MRT_services_running_on_date()`

Funktionen hanterar detta korrekt:

```php
// Prioritering:
1. Om datumet finns i exclude_dates → Kör INTE
2. Om datumet finns i include_dates → Kör (överrider veckodagar)
3. Om veckodagen är aktiverad (mon/tue/etc = 1) → Kör
```

**Exempel för 2025-07-04 (fredag):**
- Service A (vardagar): Körs normalt på fredagar, MEN...
- Service C (specialdag): Har include_dates=2025-07-04 → Körs (överrider Service A)
- Resultat: Både Service A och Service C kan köras, eller bara Service C om Service A exkluderas

## Potentiella Problem

### Problem 1: Specialdag överrider inte automatiskt vanlig service

**Scenario:**
- Fredag 2025-07-04: Service A (vardag) körs normalt
- Men ni vill att Service C (special) ska ersätta Service A den dagen

**Lösning:**
Lägg till 2025-07-04 i Service A's `exclude_dates`:
```
Service A: exclude_dates = "2025-07-04"
Service C: include_dates = "2025-07-04"
```

### Problem 2: Flera services körs samtidigt

**Scenario:**
- Om både Service A och Service C är aktiva på samma dag, kommer BÅDA att visas i tidtabellen

**Lösning:**
Detta är faktiskt korrekt beteende om ni vill visa flera services! Men om ni bara vill visa en:
- Använd `exclude_dates` för att stänga av den vanliga servicen
- ELLER: Filtrera i shortcode med `service` parameter

## Rekommendationer

### För 2-3 återanvända services:

**Struktur:**
```
Service: "Vardagstidtabell"
  ├─ Calendar: 2025-06-01 till 2025-08-31, mon-fri = 1
  └─ Stop Times: [Station 1: 09:00, Station 2: 09:15, ...]

Service: "Helgtidtabell"
  ├─ Calendar: 2025-06-01 till 2025-08-31, sat-sun = 1
  └─ Stop Times: [Station 1: 10:00, Station 2: 10:20, ...]

Service: "Högtidstidtabell" (om ni har en tredje)
  ├─ Calendar: 2025-06-01 till 2025-08-31, [specifika dagar]
  └─ Stop Times: [Station 1: 11:00, Station 2: 11:30, ...]
```

### För specialdagar:

**Struktur:**
```
Service: "Specialdag 4 juli"
  ├─ Calendar: 2025-07-04 till 2025-07-04, include_dates = "2025-07-04"
  └─ Stop Times: [Station 1: 14:00, Station 2: 14:30, ...] (HELT ANNAN TIDTABELL)

Service: "Vardagstidtabell" (uppdatera)
  └─ Calendar: exclude_dates = "2025-07-04" (så den inte körs den dagen)
```

## CSV Import Exempel

### Calendar CSV för säsong:

```csv
service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates
Vardagstidtabell,2025-06-01,2025-08-31,1,1,1,1,1,0,0,,2025-07-04
Helgtidtabell,2025-06-01,2025-08-31,0,0,0,0,0,1,1,,
Specialdag 4 juli,2025-07-04,2025-07-04,0,0,0,0,0,0,0,2025-07-04,
```

### Stop Times CSV:

```csv
service,station,sequence,arrive,depart,pickup,dropoff
Vardagstidtabell,Station 1,1,,09:00,1,1
Vardagstidtabell,Station 2,2,09:15,09:20,1,1
Vardagstidtabell,Station 3,3,09:35,,1,1

Helgtidtabell,Station 1,1,,10:00,1,1
Helgtidtabell,Station 2,2,10:20,10:25,1,1
Helgtidtabell,Station 3,3,10:45,,1,1

Specialdag 4 juli,Station 1,1,,14:00,1,1
Specialdag 4 juli,Station 2,2,14:30,14:35,1,1
Specialdag 4 juli,Station 3,3,15:00,,1,1
```

## Slutsats

### ✅ Modellen stödjer ert användningsfall!

**Stärkor:**
- ✅ Flera services kan ha olika Stop Times (olika tidtabeller)
- ✅ Calendar stödjer veckodagar för återanvändning
- ✅ `include_dates` och `exclude_dates` hanterar specialdagar
- ✅ En service kan ha flera Calendar-poster (olika perioder)

**Vad ni behöver göra:**
1. Skapa 2-3 services för era återanvända tidtabeller
2. Skapa separata services för specialdagar med sina egna Stop Times
3. Använd `exclude_dates` på vanliga services för att undvika konflikter på specialdagar
4. Använd `include_dates` på specialservices för att köra dem på rätt dagar

**Modellen är korrekt modellerad för ert användningsfall!** 🎯

