# Analys av Grön Tidtabell vs Vår Implementation

## Struktur i den Gröna Tidtabellen

### 1. **Header-sektion**
- **Titel**: "GRÖN TIDTABELL - bussanslutningar till Fjällnora"
- **Ikoner**: Visuella ikoner för varje transporttyp (Ångtåg, Rälsbuss, Dieseltåg)
- **Transporttyper**: Listade under ikonerna
- **Tågnummer**: Visas tydligt (71, 91, 73, 63, 65, 75)
- **Specialmarkering**: "Thun's-expressen" har gul vertikal bar och vertikal text

### 2. **Tidtabell-struktur**
- **Stationer i vänsterkolumn**: Listade vertikalt
- **Tåg i kolumner**: Varje tåg har sin egen kolumn
- **Tidsformat**: HH.MM (punkt istället för kolon)
- **Sektioner med rubriker**: 
  - "Från Uppsala Ö" → "Till Marielund"
  - "Från Marielund" → Selknä
  - "Från Selknä" → "Till Fjällnora" (highlighted)
  - "Från Selknä" → "Till Faringe"

### 3. **Symboler och notationer**
- **P**: Påstigning (pickup allowed, t.ex. "P 10.13")
- **X**: Tåget stannar inte vid stationen
- **|**: Tåget passerar utan att stanna (no pickup, no dropoff)
- **—**: Ingen service till den stationen
- **Tid utan prefix**: Normal stopp med både påstigning och avstigning

### 4. **Visuella markeringar**
- **Blå bakgrund**: För viktiga sektioner (Från Selknä, Till Fjällnora)
- **Gul vertikal bar**: För special services (Thun's-expressen)
- **Pilar**: Visar riktning i station-kolumnen
- **Överföringsinformation**: "Tågbyte: Dieseltåg 61, Rälsbuss 93" visas under destination

### 5. **Layout**
- **Kompakt design**: Täta borders, liten padding
- **Grön header**: För route-sektioner
- **Vit bakgrund**: För huvuddata
- **Ljusblå highlight**: För vissa rader

## Skillnader mot vår implementation

### ✅ **Vad vi redan har:**
1. ✅ Gruppering av services per route och direction
2. ✅ Visning av train types
3. ✅ Stationer i vänsterkolumn, tåg i kolumner
4. ✅ Symboler för pickup/dropoff (P, X, |)
5. ✅ Tidsformat kan konverteras till HH.MM
6. ✅ CSS-klasser för bus/special services

### ❌ **Vad som saknas eller skiljer sig:**

#### 1. **Header-struktur**
- **Saknas**: Ikoner för transporttyper
- **Saknas**: Tågnummer visas inte tydligt i header
- **Saknas**: Specialmarkeringar (gul bar, vertikal text)
- **Skillnad**: Vi visar train type + service ID, men inte som tydlig header

#### 2. **Sektionsrubriker**
- **Skillnad**: Vi visar "Från X Till Y" i route header, men inte som separata sektioner
- **Saknas**: Visuell separation mellan olika route-sektioner
- **Saknas**: "Från" och "Till" som separata rader i tabellen

#### 3. **Tidsformat**
- **Skillnad**: Vi använder HH:MM, tidtabellen använder HH.MM
- **Lösning**: Vi konverterar redan till HH.MM i overview, men inte konsekvent

#### 4. **Symboler**
- **Delvis**: Vi har P, X, | men logiken kan förbättras
- **Saknas**: Tydligare skillnad mellan "X" (stannar inte) och "|" (passerar)
- **Saknas**: "A" för avstigning (dropoff only) - vi har det i koden men använder det inte konsekvent

#### 5. **Visuella markeringar**
- **Saknas**: Blå bakgrund för viktiga rader
- **Saknas**: Gul vertikal bar för special services
- **Saknas**: Pilar för riktning
- **Delvis**: Vi har CSS-klasser men de används inte fullt ut

#### 6. **Överföringsinformation**
- **Saknas**: "Tågbyte" information under destinationer
- **Saknas**: Visning av anslutande tåg

#### 7. **Layout och design**
- **Skillnad**: Vår design är mer "WordPress-standard", tidtabellen är mer kompakt
- **Skillnad**: Vi har mer padding/spacing, tidtabellen är tätare
- **Saknas**: Grön header-stil matchar inte exakt

#### 8. **Service-nummer**
- **Skillnad**: Vi använder service ID, tidtabellen använder faktiska tågnummer (71, 91, etc.)
- **Saknas**: Fält för att ange tågnummer separat från service title

## Rekommendationer för förbättringar

### Prioritet 1 (Hög)
1. **Lägg till tågnummer-fält** i Service meta box
2. **Förbättra symbol-logik** för P/X/|/A
3. **Lägg till blå highlight** för viktiga rader (via CSS-klass)
4. **Förbättra header** med tågnummer och train type tydligare

### Prioritet 2 (Medel)
5. **Lägg till ikoner** för transporttyper (kan vara emoji eller SVG)
6. **Förbättra sektionsrubriker** med "Från X" och "Till Y" som separata rader
7. **Lägg till gul vertikal bar** för special services
8. **Förbättra kompakt design** i CSS

### Prioritet 3 (Låg)
9. **Lägg till överföringsinformation** (tågbyte)
10. **Lägg till pilar** för riktning
11. **Förbättra tidsformat** konsekvent till HH.MM

