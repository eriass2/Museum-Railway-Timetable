# Rebuild sketch – Museum Railway Timetable

Syfte: beskriva nästa version innan vi raderar eller bygger om större delar av pluginet. Detta är beslutsunderlag för en kontrollerad omstart, inte en kodradering i sig.

---

## 1. Varför vi gör en omstart

Projektet har vuxit fram stegvis och innehåller nu fungerande delar, men också många historiska beslut. Målet med omstarten är att:

- göra mockups och tryckta tidtabeller till tydliga källor för produktbeteende
- minska gammal kod och dokumentation som inte stödjer målbilden
- bygga vidare på fungerande domänlogik där den passar
- göra admin enkelt, WordPress-native och datadrivet
- göra frontend mer konsekvent med mockupflödet

---

## 2. Nuvarande funktioner

### Datamodell och import

- Stationer, rutter, tidtabeller och turer/services som WordPress post types.
- Tågtyper som taxonomy.
- Stopptider i egen tabell.
- Import av Lennakatten-referensdata från handkodade tabellstrukturer.
- Referens-PDF:er i `testdata/reference-pdfs/` för GRÖN, GUL och tidtabellsbok.

### Admin

- Dashboard med statistik, snabbknappar, settings, prisdata och demo/testverktyg.
- Meta boxes för stationer, rutter, tidtabeller och services.
- Direkt trip management från tidtabell.
- Stopptidsredigering på service.
- Import av demo-/testdata.
- Skapa demosida som visar publika shortcodes.

### Publika vyer

- `[museum_timetable_month]` – månadskalender.
- `[museum_timetable_overview]` – komplett tidtabell.
- `[museum_journey_wizard]` – enda publika reseflöde (mockup: datum, utresa, retur, priser).

### Kodkvalitet

- PHPUnit, PHPStan, PHPCS och JS-tester finns.
- Max 50 rader per funktion är etablerad regel.
- Senaste refaktorer har delat upp assets, CSS och flera renderhelpers.

---

## 3. Ny målbild

Pluginet ska i första hand vara ett WordPress-verktyg för att:

1. importera och hantera museijärnvägens tidtabellsdata
2. visa tryckt/tidtabellsliknande trafiköversikt
3. erbjuda ett publikt reseflöde enligt mockupen
4. ge admin enkla verktyg för demo/test och kvalitetssäkring

Allt som inte stöder detta ska rensas bort eller lämnas utanför rebuild.

---

## 4. Föreslagen MVP efter omstart

### Behåll i första rebuild

- Stationer.
- Rutter/linjer.
- Tågtyper.
- Tidtabeller med datum.
- Turer/services med stopptider.
- Import från referensdata.
- Månadsvy.
- Tidtabellsöversikt.
- Journey wizard som primärt publikt flöde.
- Demo/testverktyg i admin.
- Tester för import, tidtabell, resa, priser och helpers.

### Skjut upp eller ta bort tills vidare

- Legacy-vyer som inte behövs för mockupflödet.
- Duplicerad dokumentation.
- Admin-UI som inte är WordPress-native.
- Frontend-varianter som inte går att koppla till mockup eller tidtabell.
- Kod som bara finns för tidigare experiment.

---

## 5. Källor som ska sparas

Följande ska inte raderas i cleanup-steget:

- `testdata/reference-pdfs/`
- eventuella mockups när de finns i repo
- importerad/handkodad referensdata som motsvarar PDF:erna
- tester som verifierar ny målbild
- `docs/REBUILD_SKETCH.md`
- `docs/REBUILD_RULES.md`
- minimal utvecklar- och installationsdokumentation

---

## 6. Föreslagen ny struktur

Målet är tydligare ansvar per modul:

```text
inc/
├── domain/              # ren logik: datum, priser, connection search, normalisering
├── import/              # referensdata + import runners
├── admin/               # dashboard, post type UI, meta boxes, tools
├── public/              # shortcodes/rendering för publika vyer
├── assets/              # enqueue admin/frontend
├── infrastructure/      # WP adapters, db helpers, capabilities/nonces
└── bootstrap.php        # laddar moduler
```

Nuvarande struktur behöver inte bytas i ett steg. Rebuild kan börja med nya moduler och flytta över fungerande kod successivt.

---

## 7. Cleanup-plan

Cleanup ska göras efter att denna skiss är godkänd.

1. Lista varje fil som `keep`, `move`, `rewrite` eller `delete`.
2. Behåll referens-PDF:er och mockups.
3. Behåll tester som beskriver målbilden.
4. Radera dokumentation som inte längre styr produkten.
5. Radera kod som saknar plats i MVP.
6. Lägg tillbaka funktioner stegvis med tester.

---

## 8. Första rebuild-steg

1. Skapa ny minimal bootstrap.
2. Flytta/importera datamodell och import först.
3. Bygg adminverktyg för import, clear, demo.
4. Bygg tidtabellsöversikt.
5. Bygg journey wizard.
6. Lägg frontend-polish mot mockup.

Varje steg ska vara körbart och testat.
