# Style Guide – Museum Railway Timetable

Kodstandarder och clean code-principer för projektet.

---

## 1. Clean Code – Generella regler

### Läsbarhet
- **Namnge tydligt** – Variabler, funktioner och klasser ska beskriva sin syfte
- **Korta funktioner** – En funktion gör en sak, max ~50 rader
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
- **BEM-liknande** – `.mrt-block__element--modifier` vid behov
- **Variabler** – CSS custom properties med `--mrt-` prefix

### Struktur
- **CSS-variabler** i `:root` för färger, spacing, borders
- **Mobile-first** – Basstilar för mobil, `@media (min-width)` för större skärmar
- **Inga inline styles** – All styling i CSS-filer

### Exempel
```css
.mrt-timetable-group { }
.mrt-route-header { }
.mrt-time-cell.mrt-service-bus { }
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

```
museum-railway-timetable/
├── museum-railway-timetable.php   # Huvudfil
├── uninstall.php
├── inc/
│   ├── functions/                 # Helper-funktioner
│   ├── admin-*.php                # Admin-specifikt
│   ├── assets.php
│   ├── cpt.php
│   └── shortcodes.php
├── assets/
│   ├── admin.css
│   ├── admin.js
│   └── frontend.js
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

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Clean Code (Robert C. Martin)](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882)
