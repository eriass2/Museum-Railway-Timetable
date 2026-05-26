# Style Guide – Museum Railway Timetable

Kodstandarder och clean code-principer för projektet (PHP, CSS, JS, WordPress).

**Arkitektur och rebuild-design:** [REBUILD_RULES.md](REBUILD_RULES.md) och [ARCHITECTURE.md](ARCHITECTURE.md). Denna guide fokuserar på namngivning, säkerhet och filkonventioner så att regler inte dupliceras.

---

## 1. Clean Code – Generella regler

### Läsbarhet
- **Namnge tydligt** – Variabler, funktioner och klasser ska beskriva sin syfte
- **Korta funktioner** – En funktion gör en sak, max ~50 rader
- **Max metodlängd** – Hård regel: max 50 rader per funktion/metod. Överskrids detta, dela upp i mindre funktioner (extract method)
- **Undvik djup nästling** – Max 3–4 nivåer; refaktorera vid behov
- **Kommentera varför, inte vad** – Koden ska vara självförklarande; kommentera affärslogik

### DRY (Don't Repeat Yourself)
- **Extrahera återanvändbar logik** till helper-funktioner
- **Undvik duplicerad kod** – Om samma logik finns på flera ställen, skapa en gemensam funktion

### Single Responsibility
- Varje funktion har ett ansvar
- Varje fil har ett tydligt syfte

### Förenkling
- **YAGNI** – Implementera inte saker "för framtiden"
- **KISS** – Välj den enklaste lösningen som fungerar
- **Undvik premature optimization** – Optimera först när det behövs

### Felhantering
- **Fail fast** – Upptäck fel tidigt, returnera eller kasta tydligt
- **Validera input** – Kontrollera data vid ingång till funktioner
- **Tydliga felmeddelanden** – Hjälp utvecklaren att förstå vad som gick fel

---

## 2. PHP

### Namnkonventioner
| Element | Konvention | Exempel |
|---------|------------|---------|
| Funktioner | `MRT_` + snake_case | `MRT_get_service_stop_times()` |
| Hooks (actions/filters) | `mrt_` prefix | `mrt_overview_days_ahead` |
| Meta keys | `mrt_` prefix | `mrt_service_number` |
| Post types | `mrt_` prefix | `mrt_station`, `mrt_timetable` |
| Taxonomier | `mrt_` prefix | `mrt_train_type` |

### Säkerhet
- **ABSPATH** – Alla PHP-filer (utom `uninstall.php`) ska ha: `if (!defined('ABSPATH')) { exit; }`
- **Escape all output** – Använd `esc_html()`, `esc_attr()`, `esc_url()` etc.
- **Sanitize input** – `sanitize_text_field()`, `intval()`, `wp_kses()` etc.
- **Nonces** – Alla formulär och AJAX-anrop ska använda nonces
- **Capability checks** – `current_user_can()` för admin-funktioner
- **SQL** – Alltid `$wpdb->prepare()` för parametriserade queries

### Dokumentation
- **PHPDoc** för alla funktioner med `@param`, `@return`, `@throws`
- **Text domain** – Alltid `museum-railway-timetable` för översättningar

### Övrigt
- **Inga inline styles** i PHP – använd CSS-klasser
- **Inga `echo` av oräddad data** – alltid escape först

---

## 3. CSS

### Namnkonventioner
- **Prefix** – Alla klasser: `.mrt-` (t.ex. `.mrt-timetable-overview`)
- **BEM-liknande** – `.mrt-block--modifier` (t.ex. `.mrt-btn--primary`)
- **Variabler** – CSS custom properties med `--mrt-` prefix

### Struktur
- **Rebuild-status** – Nuvarande utseendeimplementation är purgad. Ny CSS ska byggas från mockups enligt `REBUILD_RULES.md`.
- **UI-klasser** – Nya klasser ska använda `.mrt-*` och vara BEM-liknande där det behövs.
- **CSS-variabler** – Använd semantiska `--mrt-*` tokens när ny design byggs upp.
- **Mobile-first** – Basstilar för mobil, `@media (min-width)` för större skärmar.
- **Inga inline styles** – All styling i CSS-filer.

### Exempel
```html
<button class="mrt-button mrt-button--primary">Spara</button>
<div class="mrt-card">...</div>
<div class="mrt-form-field">...</div>
```

---

## 4. JavaScript

### Struktur
- **IIFE** – Wrappas i Immediately Invoked Function Expression
- **jQuery** – Använd `$` för DOM-manipulation
- **Ingen `console.log`** i produktion – endast med debug-flagga

### Namnkonventioner
- **camelCase** för variabler och funktioner
- **Prefix** för plugin-specifika: `mrtAdmin`, `mrtFrontend` etc.

### Event och AJAX
- **Nonces** – Skicka alltid med AJAX-anrop
- **Felhantering** – Hantera nätverksfel och visa användarvänliga meddelanden

### Delade util-moduler (`assets/mrt-*.js`)
- **`mrt-string-utils.js`** – `window.MRTStringUtils.escapeHtml` (XSS-säker text i HTML-strängar). **`admin-utils.js`** `escapeHtml` delegerar hit.
- **`mrt-date-utils.js`** – `window.MRTDateUtils` (format av `YYYY-MM-DD`, kalenderbyggstenar, `validateHhMm` för `HH:MM`). **`admin-utils.js`** `validateTimeFormat` delegerar till `MRTDateUtils.validateHhMm`.
- **`mrt-frontend-api.js`** – `window.MRTFrontendApi`: `getAjaxUrl`, `getNonce`, `msg` (strängar från `mrtFrontend`), `post` med valfri override av URL/nonce (t.ex. wizard). Laddas före `frontend.js`; används av `frontend.js` och kan användas av andra frontend-skript med samma beroenden.
- **`admin-utils.js`** – `window.MRTAdminUtils.msg(key, fallback)` för strängar från `mrtAdmin` (samma mönster som `MRTFrontendApi.msg`). Använd i admin-moduler i stället för upprepade `typeof mrtAdmin`-tester.
- **Lägg ny återanvändbar logik** i rätt util-fil i stället för att duplicera i flera skript.
- **Enqueue** – `inc/assets.php` laddar `inc/assets/loader.php` (admin + frontend enqueue) (admin: bl.a. `mrt-string-utils` före `mrt-admin-utils`; frontend: `mrt-string-utils` + `mrt-frontend-api` före `mrt-frontend`; wizard + tidtabellsöversikt även `train-type-icons.css`).
- **JS-tester (valfritt)** – `composer test:js` kör `node --test tests/js/` (Node 18+); täcker delade util-filer utan browser.

---

## 5. WordPress-specifikt

### Hooks
- **Actions** – `add_action('hook_name', 'callback', 10, 1)`
- **Filters** – `add_filter('mrt_filter_name', 'callback', 10, 2)`
- **Prefix** – Alla custom hooks: `mrt_`

### Översättning
- **Text domain** – `museum-railway-timetable`
- **Funktioner** – `__()`, `esc_html__()`, `esc_attr__()`, `_n()` etc.
- **Kontext** – Använd `_x()` vid behov för kontextberoende strängar

### Databas
- **Tabellprefix** – `$wpdb->prefix . 'mrt_stoptimes'`
- **Prepared statements** – Alltid för dynamiska queries

---

## 6. Filstruktur

### Mappar
- **Använd mappar** – Organisera kod efter ansvar (`inc/domain/`, `inc/admin/`, `inc/infrastructure/`, `inc/public/`)
- **En fil per ansvar** – Varje mapp innehåller filer med tydligt, sammanhörande ansvar
- **Loader-filer** – Tunna loaders (`inc/infrastructure/ajax.php`, `inc/admin/meta-boxes.php`) require:ar undermappar

### Struktur

Se [ARCHITECTURE.md](ARCHITECTURE.md) för full `inc/`-karta. Kort:

```
museum-railway-timetable/
├── museum-railway-timetable.php   # Huvudfil → inc/bootstrap.php
├── uninstall.php
├── docs/                          # Index: docs/README.md
├── scripts/                       # validate.php, docker-dev-reset.ps1, …
├── inc/
│   ├── bootstrap/                 # domain loader
│   ├── domain/                    # affärslogik (journey, service, timetable, …)
│   ├── infrastructure/            # CPT, ajax/, wordpress/
│   ├── admin/                     # dashboard/, meta-boxes/, tools/
│   ├── public/                    # month-calendar, timetable-overview, journey-wizard
│   ├── import/lennakatten/
│   ├── assets/                    # enqueue-hjälpare
│   ├── admin.php, shortcodes.php, assets.php
│   └── constants.php
├── assets/
│   ├── admin.css, admin.js, admin-*.js
│   ├── frontend.js, frontend-public.css, frontend-overview.css
│   ├── journey-wizard.js, journey-wizard.css, journey-wizard/
│   ├── mrt-string-utils.js, mrt-date-utils.js, mrt-frontend-api.js
│   └── icons/train-types/
└── languages/
```

---

## 7. Contributing – Snabbchecklista

- [ ] Följer WordPress coding standards
- [ ] PHPDoc på alla nya funktioner
- [ ] All output escaped
- [ ] All input sanitized
- [ ] Nonces på formulär/AJAX
- [ ] Inga inline styles
- [ ] CSS-klasser med `.mrt-` prefix
- [ ] Funktioner med `MRT_` prefix
- [ ] Översättningsfunktioner med text domain
- [ ] Testerat manuellt

---

## 8. Referenser

- **REBUILD_RULES.md** – Rebuild-regler för kod, design och kvalitet
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Clean Code (Robert C. Martin)](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882)
