# Översikt över Shortcodes och Komponenter

## Shortcodes (3 st)

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

Använd train type-slug från **Railway Timetable → Train Types** (demo-import: `angtag`, `ralsbuss`, `dieseltag`, `buss`, `ang-diesel`).

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

### 3. `[museum_journey_wizard]` - Reseplanerare (flersteg)
Mockup-liknande flöde: rutt → datum (kalender med trafiklägen) → utresa → ev. retur → sammanfattning med prismatris. Direktresor och byte. Tågtypsikoner i resultat. Kräver JavaScript.

**Användning:**
```
[museum_journey_wizard ticket_url="https://example.com/biljetter" hero_subtitle="" timetable_id="123"]
```

**Parametrar:**
- `ticket_url` – Länk till biljett/bokning (valfritt; knapp i sista steget)
- `hero_subtitle` – Underrubrik steg 1 (valfritt)
- `timetable_id` – Visar utfällbar tidtabellsöversikt under sökformuläret på steg 1 (valfritt)
- `timetable` – Samma som `timetable_id` men med exakt tidtabellstitel (valfritt)
- `embedded` – `1` / `true` för kompakt layout inuti sidinnehåll (t.ex. component demo), utan fullbredds-hero

**Backend (AJAX):** `mrt_search_journey`, `mrt_journey_calendar_month`, `mrt_journey_connection_detail` (se [Journey – backend](#journey--backend)).

**Se även:** [ACCESSIBILITY.md](ACCESSIBILITY.md) (WCAG, release-rökning)

---

## WordPress Widgets

**Inga widgets är för närvarande registrerade.**

Shortcodes kan användas i widgets genom text-widgets eller custom HTML-widgets.

---

## Journey – backend

Delad journey-domän och AJAX (används av wizarden):

- **Domän:** `inc/domain/journey/`
- **AJAX:** `inc/infrastructure/ajax/journey.php`, `journey-parse.php`
- **Delade JS:** `mrt-string-utils.js`, `mrt-date-utils.js`, `mrt-frontend-api.js`, `assets/journey-wizard/` (moduler, se README där)

---

## Frontend Assets

Vid användning på webbplatsen laddar plugin relevanta filer via `inc/assets.php`, bland annat:

- **Månad:** `assets/frontend.js` (kalender-AJAX)
- **Wizard:** `assets/journey-wizard/*.js` + `assets/journey-wizard.css`
- **Tågtypsikoner:** `assets/train-type-icons.css`

Assets köas när motsvarande shortcode finns på sidan (eller via filter `mrt_should_enqueue_frontend_assets`).

---

## Component demo (utveckling)

**Railway Timetable → Component demo page** (eller `docker-dev-reset.ps1`) skapar en sida med tre block:

1. Månadskalender  
2. Tidtabellsöversikt (GRÖN efter Lennakatten-import)  
3. Journey wizard (`embedded="1"`)

Se [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

---

## Framtida Förbättringar

Möjliga framtida tillägg:

- WordPress Widgets för varje shortcode-typ
- Gutenberg Blocks för varje shortcode-typ
- Mer avancerade filter- och sorteringsalternativ
- Export-funktionalitet för tidtabeller
