# Style Guide Review – Museum Railway Timetable

Granskning av projektet mot STYLE_GUIDE.md (2025-02-17).

---

## ✅ Det som följer style guide

### PHP
- **ABSPATH** – Alla PHP-filer har korrekt check (uninstall.php använder WP_UNINSTALL_PLUGIN)
- **Funktionsprefix** – Alla funktioner använder `MRT_`
- **Hooks** – Custom hooks använder `mrt_` prefix
- **Post types / meta** – `mrt_station`, `mrt_timetable`, `mrt_service_number` etc.
- **SQL** – Dynamiska queries använder `$wpdb->prepare()`
- **Nonces** – Formulär och AJAX använder nonces
- **Text domain** – Konsekvent `museum-railway-timetable`

### CSS
- **Prefix** – Nästan alla klasser har `.mrt-` prefix
- **CSS-variabler** – `--mrt-` i `:root`
- **Mobile-first** – Media queries med `max-width` för mobil

### JavaScript
- **IIFE** – admin.js och frontend.js wrappas i IIFE
- **jQuery** – Används konsekvent
- **console.log** – Endast inom `if (window.mrtDebug)`
- **AJAX nonces** – Skickas med alla AJAX-anrop

### Clean Code
- **Helper-funktioner** – Återanvändbar logik i `inc/functions/helpers.php`
- **Filstruktur** – Tydlig uppdelning (inc/, assets/, languages/)

---

## ⚠️ Avvikelser och förbättringar

### 1. Inline styles i PHP (Style Guide: "Inga inline styles")

**Förekomster:**
- `inc/shortcodes.php:125` – `style="display: none;"`
- `inc/functions/timetable-view.php:406` – `style="--service-count: ..."` (CSS-variabel, kan vara OK)
- `inc/admin-page.php` – flera `style="margin-top: 1rem;"` etc.
- `inc/admin-meta-boxes.php` – många inline styles (margin, padding, width, color)

**Rekommendation:** Flytta layout/margin/padding till CSS-klasser. Undantag: `--service-count` är dynamisk CSS-variabel och kan behållas.

### 2. `$selected` i shortcodes.php (rad 196, 211)

```php
<?php echo $selected; ?>
```

`$selected` kommer från `selected()` som returnerar säker HTML. Tekniskt OK, men för tydlighet: `selected()` är WordPress core-funktion som returnerar escaped output. Inget säkerhetsproblem.

### 3. TRUNCATE utan prepare (admin-page.php:500)

```php
$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}mrt_stoptimes");
```

TRUNCATE har ingen användarinput – tabellnamnet är från `$wpdb->prefix`. Säker, men style guide säger "Alltid prepare för dynamiska queries". Här är det statiskt tabellnamn – acceptabelt.

### 4. `validate.php` – ingen ABSPATH

CLI-skript som körs med `php validate.php` – laddar inte WordPress. ABSPATH är inte relevant. OK.

### 5. CSS: `.wrap h1` (admin.css:75)

WordPress admin-klass. Vi stylar WP admin-element – acceptabelt. Övriga egna klasser följer `.mrt-`.

### 6. Långa funktioner (>50 rader)

Några funktioner överskrider ~50 rader, t.ex.:
- `MRT_render_timetable_meta_box` – mycket HTML
- `MRT_render_service_stoptimes_box` – komplex
- `initTimetableServicesUI` i admin.js

**Rekommendation:** Överväg att bryta ut delar i mindre funktioner vid nästa refaktorering.

### 7. XSS-risk i admin.js (rad 792)

```javascript
options += '<option value="' + dest.id + '">' + dest.name + '</option>';
```

`dest.name` kommer från `post_title` (databas). Vid HTML-injection kan skadlig kod köras. **Åtgärd:** Escapa vid insättning, t.ex. med `document.createElement` + `textContent` eller en escape-funktion.

---

## Sammanfattning

| Kategori | Status | Antal avvikelser |
|----------|--------|------------------|
| PHP säkerhet | ✅ Bra | 0 |
| PHP namnkonventioner | ✅ Bra | 0 |
| Inline styles | ⚠️ Avvikelse | ~25 |
| CSS | ✅ Bra | 0 |
| JavaScript | ✅ Bra | 0 |
| Clean Code | ⚠️ Delvis | Långa funktioner |

**Slutsats:** Projektet följer style guide väl i säkerhet och namnkonventioner. Huvudsakliga förbättringsområden:**

1. **Flytta inline styles till CSS** – särskilt i admin-meta-boxes.php
2. **Bryta upp långa funktioner** – vid refaktorering
3. **Verifiera att AJAX-svar är escaped** – för destinations-dropdown

---

## Prioriterad åtgärdslista

1. ~~**Låg** – Inline styles → CSS-klasser (admin-meta-boxes, admin-page)~~ ✅ Åtgärdat 2025-02-17
2. ~~**Låg** – Refaktorera mycket långa funktioner~~ ✅ Åtgärdat 2025-02-17 (MRT_render_stoptime_row extraherad, populateDestinationsSelect/setSelectState i admin.js)
3. ~~**Medel** – Verifiera escape av `dest.name` i route destinations AJAX~~ ✅ Åtgärdat 2025-02-17 (document.createElement + textContent)
