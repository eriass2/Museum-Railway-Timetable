# Översikt över Shortcodes och Komponenter

## Shortcodes (4 st)

### 1. `[museum_timetable_month]` - Månadsvy
Visar en kalendermånadsvy som visar vilka dagar som har turer.

**Användning:**
```
[museum_timetable_month month="2025-06" train_type="" service="" legend="1" show_counts="1"]
```

**Parametrar:**
- `month` - Månad i YYYY-MM format (standard: aktuell månad)
- `train_type` - Filtrera efter train type slug (valfritt)
- `service` - Filtrera efter exakt service title (valfritt)
- `legend` - Visa förklaring (0 eller 1, standard: 1)
- `show_counts` - Visa antal turer per dag (0 eller 1, standard: 1)
- `start_monday` - Börja veckan på måndag (0 eller 1, standard: 1)
- `nav` - Visa länkar föregående/nästa månad (0 eller 1, standard: 1)

**Exempel:**
```
[museum_timetable_month month="2025-06" train_type="angtag" show_counts="1"]
```

Använd train type-slug från **Railway Timetable → Train Types** (demo-import: `angtag`, `ralsbuss`, `dieseltag`, `buss`).

**Funktioner:**
- Klickbara dagar som visar tidtabell för vald dag
- Visar antal turer per dag
- Filtrering efter train type eller service

---

### 2. `[museum_timetable_overview]` - Komplett Tidtabell
Visar en komplett tidtabell-översikt grupperad per route och riktning.

**Användning:**
```
[museum_timetable_overview timetable_id="123"]
```

**Parametrar:**
- `timetable_id` - Timetable post ID (rekommenderat)
- `timetable` - Timetable namn (alternativ till timetable_id)

**Vad den visar:**
- Alla turer (services) i tidtabellen
- Grupperade per route och riktning (t.ex. "Från Uppsala Ö Till Marielund")
- Train types med PNG-ikoner (ång, diesel, rälsbuss, buss) i `assets/icons/train-types/`
- Tågnummer (eller service ID som fallback)
- Ankomst/avgångstider i HH.MM format för varje station
- Symboler: P (pickup only), A (dropoff only), X (no time), | (passes without stopping)
- Överföringsinformation som visar anslutande tåg vid destinationsstationer
- Riktningspilar (↓) för första och sista stationen
- Gul markering för namngivna express-turer (när tågtyp eller titel innehåller ”express”, t.ex. Thun’s-expressen)

**Exempel:**
```
[museum_timetable_overview timetable_id="123"]
[museum_timetable_overview timetable="Sommar 2025"]
```

---

### 3. `[museum_journey_wizard]` - Reseplanerare (flerssteg)
Mockup-liknande flöde: rutt → datum (kalender med trafiklägen) → utresa → ev. retur → sammanfattning med prismatris. Direktresor och byte. Tågtypsikoner i resultat. Samma AJAX som plannern (`mrt_search_journey`, `mrt_journey_calendar_month`, `mrt_journey_connection_detail`). Kräver JavaScript.

**Användning:**
```
[museum_journey_wizard ticket_url="https://example.com/biljetter" hero_image="" hero_subtitle="" timetable_id="123"]
```

**Parametrar:**
- `ticket_url` – Länk till biljett/bokning (valfritt; knapp i sista steget)
- `hero_image` – URL till bakgrundsbild steg 1 (valfritt)
- `hero_subtitle` – Underrubrik steg 1 (valfritt)
- `timetable_id` – Visar utfällbar tidtabellsöversikt under sökformuläret på steg 1 (valfritt)
- `timetable` – Samma som `timetable_id` men med exakt tidtabellstitel (valfritt)

**Se även:** [ACCESSIBILITY.md](ACCESSIBILITY.md) (WCAG, release-rökning)

---

### 4. `[museum_journey_planner]` - Reseplanerare (en skärm)
Visar en reseplanerare där användare kan söka efter anslutningar mellan två stationer på **en sida** (formulär + resultat). Samma backend som wizard.

**Användning:**
```
[museum_journey_planner]
```

**Parametrar:**
- `default_date` - Förvalt datum i YYYY-MM-DD format (valfritt, standard: idag)

**Vad den visar:**
- Dropdown för att välja avgångsstation (From)
- Dropdown för att välja ankomststation (To)
- Datumväljare (standard: dagens datum)
- Sökknapp för att hitta anslutningar
- Resultattabell med direktresor och anslutningar med ett byte (när giltigt)
- Avgångs-/ankomsttider, tågtyp och tågnummer per delsträcka

**Exempel:**
```
[museum_journey_planner]
[museum_journey_planner default_date="2025-06-15"]
```

**Funktioner:**
- Söker direktresor och enkelbyte som kör på datumet, respekterar stoppordning och pickup/dropoff
- Resultat sorteras efter avgångstid
- För flerstegsflöde med retur och priser: använd `[museum_journey_wizard]`

---

## WordPress Widgets

**Inga widgets är för närvarande registrerade.**

Shortcodes kan dock användas i widgets genom att lägga till dem i text-widgets eller custom HTML-widgets.

---

## Journey – backend (wizard / planner)

- **Domän:** `inc/functions/journey-*.php`
- **AJAX:** `inc/admin-ajax/journey.php`, `journey-parse.php`, `journey-render.php`
- **Delade JS:** `mrt-string-utils.js`, `mrt-date-utils.js`, `mrt-frontend-api.js` (se [STYLE_GUIDE.md](STYLE_GUIDE.md) §4)

---

## Frontend Assets

Vid användning på webbplatsen laddar plugin relevanta filer via `inc/assets.php` (loader som inkluderar `inc/assets/admin.php` och `inc/assets/frontend.php`), bland annat:
- Gemensam bas: `admin-base.css`, komponenter, tidtabell-CSS, m.m.
- **Månad / översikt / enkel planner:** `assets/frontend.js` (AJAX)
- **Wizard:** `assets/journey-wizard.css` + `assets/journey-wizard.js` + `assets/train-type-icons.css` (när `[museum_journey_wizard]` finns i innehållet)

Assets köas när motsvarande shortcode finns på sidan (eller via filter `mrt_should_enqueue_frontend_assets`).

---

## Framtida Förbättringar

Möjliga framtida tillägg:
- WordPress Widgets för varje shortcode-typ
- Gutenberg Blocks för varje shortcode-typ
- Mer avancerade filter- och sorteringsalternativ
- Export-funktionalitet för tidtabeller
