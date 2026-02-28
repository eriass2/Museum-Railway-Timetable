# Jämförelse: Lennakatten PDF 2026 vs vår implementation

## Översikt

Denna jämförelse baseras på *Lennakatten folder 2026.pdf* (GRÖN, RÖD, GUL, ORANGE tidtabeller).

---

## ✅ Det som stämmer

### Tabellstruktur
| Element | PDF | Vår implementation |
|---------|-----|-------------------|
| Header rad 1 (tågtyper) | Ångtåg, Rälsbuss, Dieseltåg | ✅ `.mrt-header-train-type` |
| Header rad 2 (tågnummer) | 71, 93, 75, 63, 65, 79 | ✅ `.mrt-header-train-number` |
| Station-kolumn | Spänner 2 rader | ✅ `.mrt-station-col-header` |
| "Från [station]" rad | Blå bakgrund | ✅ `.mrt-from-row` (#e3f2fd, #90caf9) |
| "Till [station]" rad | Blå bakgrund | ✅ `.mrt-to-row` |
| "Tågbyte:" rad | Gul bakgrund, 2 rader | ✅ `.mrt-transfer-row` (#fff9c4, #fff59d) |
| Vanliga stationer | Vit/alternerande | ✅ `.mrt-grid-row` |

### Färgkodning
| Element | PDF | Vår CSS |
|---------|-----|---------|
| Buss-kolumner (Rälsbuss) | Blå bakgrund | ✅ `--mrt-bus-bg: #e3f2fd` |
| Specialtåg (Thun's-expressen) | Gul bakgrund | ✅ `--mrt-special-bg: #fff9c4` + gul vänsterkant |
| Från/Till rader | Blå | ✅ `#e3f2fd`, `#90caf9` |
| Tågbyte rad | Gul | ✅ `#fff9c4`, `#fff59d` |

### Symboler
| Symbol | PDF | Vår implementation |
|--------|-----|-------------------|
| P | Påstigning endast | ✅ `MRT_format_stop_time_display()` |
| X | Av/påstigning | ✅ |
| — | Tåget kör inte | ✅ `:empty::before { content: '—' }` |
| \| | Passerar utan att stanna | ✅ (enligt helpers.php) |

### Tidsformat
- PDF: `10.00`, `P 10.03`, `X 10.09`
- Vår: ✅ `MRT_format_time_display()` → HH.MM med punkt

### Tågbyte-rad
- PDF: 2 rader – rad 1: tågtyp (Dieseltåg, Rälsbuss), rad 2: tågnummer (61, 97)
- Vår: ✅ `.mrt-transfer-train-type` + `.mrt-transfer-service-number`

---

## ⚠️ Skillnader och förbättringar

### 1. Thun's-expressen placering
**PDF:** "Thun's-expressen" visas under tågnumret i headern OCH ibland i första datacellen (t.ex. under "11.10" i Från Uppsala Östra-raden).

**Vår:** Endast i header som `.mrt-special-label`.

**Förslag:** Nuvarande lösning är rimlig – det räcker att visa det i headern.

---

### 2. Buss-sektioner med asterisk (*)
**PDF:** Har rader som "Från Selknä*" och "Till Fjällnora*" för bussanslutningar. Asterisken markerar buss.

**Vår:** Ingen särskild hantering av * i stationsnamn.

**Förslag:** Om ni vill matcha PDF kan ni:
- Lägga till stöd för `*` i stationsnamn (t.ex. "Selknä*")
- Eller en CSS-klass `.mrt-bus-section` för dessa rader

---

### 3. Tomma celler i Tågbyte-raden
**PDF:** Tomma celler i Tågbyte-raden är helt tomma (ingen "—").

**Vår:** `.mrt-time-cell:empty::before { content: '—' }` sätter "—" i alla tomma celler.

**Förslag:** Lägg till `.mrt-transfer-train-type:empty::before` och `.mrt-transfer-service-number:empty::before { content: none }` så Tågbyte-celler förblir tomma när det inte finns byte.

---

### 4. Stationstavning
**PDF 2026:** "Skölsta" (med ö)  
**TIMETABLE_STRUCTURE.md:** Uppdaterat till "Skölsta" för att matcha PDF.

Kontrollera att stationer i databasen använder "Skölsta" (enligt PDF).

---

### 5. Tidtabell-titlar (GRÖN, RÖD, GUL, ORANGE)
**PDF:** Varje tidtabell har en titel (t.ex. "GRÖN TIDTABELL", "RÖD TIDTABELL") med giltighetsperiod.

**Vår:** Route-header visar "Från X Till Y" men inte tidtabellens färg/namn.

**Förslag:** Om Timetable-posttypen har ett fält för "tidtabellstyp" (grön/röd/gul/orange) kan det visas i headern.

---

### 6. "Buss" som tågtyp
**PDF:** En kolumn kan ha "Buss" som tågtyp (t.ex. RÖD tidtabell, sista kolumnen).

**Vår:** `mrt-service-bus` sätts för "Rälsbuss" och "buss" i train_type. Kontrollera att "Buss" (utan "Räls") också får blå styling.

---

## HTML-struktur (vår vs PDF-liknande)

### Vår grid-struktur
```
.mrt-overview-grid
├── .mrt-grid-header (display: contents)
│   ├── .mrt-station-col-header (row 1-2)
│   ├── .mrt-header-train-type × N (row 1)
│   ├── .mrt-station-col-header-empty
│   └── .mrt-header-train-number × N (row 2)
└── .mrt-grid-body (display: contents)
    ├── .mrt-from-row
    ├── .mrt-grid-row × N (vanliga stationer)
    ├── .mrt-to-row
    ├── .mrt-transfer-row (tågtyp)
    └── .mrt-transfer-row (tågnummer)
```

Detta motsvarar PDF-strukturen.

---

## CSS som kan justeras

### Tomma Tågbyte-celler
```css
/* Lägg till i admin.css - tomma celler i Tågbyte ska inte visa "—" */
.mrt-transfer-train-type:empty::before,
.mrt-transfer-service-number:empty::before {
    content: none !important;
}
```

### Buss-rad (om ni lägger till stöd för *)
```css
.mrt-bus-section .mrt-station-col::after {
    content: ' *';
    font-size: 0.85em;
    opacity: 0.8;
}
```

---

## Sammanfattning

| Kategori | Status |
|----------|--------|
| Tabelllayout | ✅ Matchar |
| Färger (blå, gul) | ✅ Matchar |
| Symboler (P, X, —) | ✅ Matchar |
| Tågbyte 2 rader | ✅ Matchar |
| Thun's-expressen | ✅ Header + första datacell |
| Buss-asterisk (*) | ✅ Checkbox på stationer |
| Tomma Tågbyte-celler | ✅ Tomma (som PDF) |
| Tidtabell-titlar | ✅ GRÖN/RÖD/GUL/ORANGE |
| Stavning Skölsta | ✅ Uppdaterad i dokumentation |

**Slutsats:** Implementationen matchar nu PDF:en. Alla fem förbättringar är implementerade.
