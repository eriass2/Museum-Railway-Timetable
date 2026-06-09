# Översikt över Shortcodes och Komponenter

## Shortcodes (4 st)

### 1. `[museum_traffic_notices]` - Trafikmeddelanden

Visar generella trafikmeddelanden och tur-avvikelser för idag (valfritt imorgon). Tom vy: «Inga meddelanden». Kräver JavaScript.

**Användning:**
```
[museum_traffic_notices]
```

**Parametrar:**
- `days` – `1` = idag (standard), `2` = idag + imorgon
- `date` – Referensdatum `YYYY-MM-DD` (test; standard: WP-tid idag)
- `show_general` – Visa generella meddelanden (`0` eller `1`, standard: `1`)
- `show_deviations` – Visa tur-avvikelser (`0` eller `1`, standard: `1`)
- `title` – Valfri rubrik ovanför listan

**Exempel (startsida):**
```
[museum_traffic_notices]
[museum_timetable_month ...]
```

Generella meddelanden redigeras i admin under **Trafikmeddelanden** (`#/traffic-notices`). Tur-avvikelser redigeras som tidigare under **Tidtabell → Avvikelser**.

**Backend (REST):** `GET /museum-railway-timetable/v1/traffic-notices` (se [REST_API.md](REST_API.md)).

---

### 2. `[museum_timetable_month]` - Månadsvy
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

### 3. `[museum_timetable_overview]` - Komplett Tidtabell
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

### 4. `[museum_journey_wizard]` - Reseplanerare (flersteg)
Mockup-liknande flöde: rutt → datum (kalender med trafiklägen) → utresa → ev. retur → sammanfattning med prismatris. Direktresor och byte. Tågtypsikoner i resultat. Kräver JavaScript.

**Användning:**
```
[museum_journey_wizard ticket_url="https://example.com/biljetter" timetable_page_url="https://example.com/tidtabell"]
```

**Parametrar:**
- `route_title` – Rubrik på steg 1 (standard: ”Planera resa”). Exempel: `route_title="Planera resa med Lennakatten"`
- `ticket_url` – *(inaktiverat)* Reserverat attribut; knappen visas inte i nuvarande version
- `timetable_page_url` – Länk till separat tidtabellssida (valfritt; visas under sök på steg 1)
- `hero_subtitle` – *(föråldrad, ignoreras)* – användes tidigare som underrubrik
- `timetable_id` – *(legacy)* – inbäddad tidtabell under steg 1; rekommenderas inte i Vue-flödet
- `timetable` – Samma som `timetable_id` men med exakt tidtabellstitel (valfritt)
- `embedded` – `1` / `true` för kompakt layout inuti sidinnehåll (t.ex. component demo), utan fullbredds-hero

**Backend (REST):** `GET /mrt/v1/journey/search`, `GET /mrt/v1/journey/calendar`, `GET /mrt/v1/journey/connection-detail` (se [Journey – backend](#journey--backend)).

**Se även:** [ACCESSIBILITY.md](ACCESSIBILITY.md) (WCAG, release-rökning)

---

## WordPress Widgets

**Inga widgets är för närvarande registrerade.**

Shortcodes kan användas i widgets genom text-widgets eller custom HTML-widgets.

---

## Journey – backend

Delad journey-domän och REST (används av wizarden):

- **Domän:** `inc/domain/journey/`
- **REST:** `inc/infrastructure/rest/public/journey-public.php` (`/mrt/v1/journey/*`)
- **Publik frontend (Vue):** `frontend/vue/` → `assets/dist/vue/` (se [VUE_FRONTEND.md](VUE_FRONTEND.md))

---

## Frontend Assets

Plugin laddar **en** Vite ES-modul (`assets/dist/vue/assets/main-*.js`) med CSS (importerad från `assets/*.css`). Varje Vue-app (`month`, `overview`, `wizard`, `index`, `traffic_notices`) laddas som async chunk när shortcoden mountas på sidan.

- **Enqueue:** `inc/assets/vue-frontend.php`, `inc/assets/frontend.php`
- **Tågtypsikoner:** bundlade via `frontend/vue/src/styles/mrt-public.css`

Assets köas när motsvarande shortcode finns i sidans innehåll (eller när shortcode renderas / filter `mrt_should_enqueue_frontend_assets`).

---

## Component demo (utveckling)

**Railway Timetable → Component demo page** (eller `docker-dev-reset.ps1`) skapar en sida med fyra block:

1. Trafikmeddelanden  
2. Månadskalender  
3. Tidtabellsöversikt (GRÖN efter Lennakatten-import)  
4. Journey wizard (`embedded="1"`)

Se [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md).

---

## Framtida Förbättringar

Möjliga framtida tillägg:

- WordPress Widgets för varje shortcode-typ
- Gutenberg Blocks för varje shortcode-typ
- Mer avancerade filter- och sorteringsalternativ
- Export-funktionalitet för tidtabeller
