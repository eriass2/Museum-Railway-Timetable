# PHP – Granskning mot Style Guide

Granskning av `*.php` mot STYLE_GUIDE.md (sektion 2. PHP).

**Senast analyserad:** 2025-02-17

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **ABSPATH** | Alla PHP-filer (utom uninstall.php, validate.php, phpstan-bootstrap) har `if (!defined('ABSPATH')) { exit; }` |
| **uninstall.php** | Använder `WP_UNINSTALL_PLUGIN` enligt WordPress-konvention |
| **Funktionsnamn** | `MRT_` + snake_case konsekvent |
| **Hooks** | `mrt_` prefix |
| **Meta keys, post types, taxonomier** | `mrt_` prefix |
| **Escape output** | `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()` används i output |
| **Sanitize input** | `sanitize_text_field()`, `intval()`, `sanitize_title()` på $_GET/$_POST |
| **Nonces** | Formulär och AJAX använder nonces, verifieras med wp_verify_nonce/check_ajax_referer |
| **Capability checks** | `current_user_can()`, `MRT_verify_ajax_permission()` för admin |
| **SQL** | `$wpdb->prepare()` för parametriserade queries |
| **Text domain** | `museum-railway-timetable` / MRT_TEXT_DOMAIN konsekvent |
| **PHPDoc** | Många funktioner har @param, @return |

---

## ⚠️ Förbättringar

### 1. Nonce – sanitize före wp_verify_nonce ✓ (genomfört)

- **route-destinations.php**, **route-stations.php**: Sanitize nonce med `sanitize_text_field(wp_unslash($_POST['nonce']))`

### 2. $_GET – sanitize ✓ (genomfört)

- **clear-db.php**: `$_GET['mrt_cleared']` sanitizes med `sanitize_text_field(wp_unslash())`

### 3. MRT_render_info_box – $content

**inc/functions/helpers-utils.php** (rad 83):
```php
echo $content;
```
- Docblock säger att anroparen ska använda esc_html eller wp_kses_post
- **Åtgärd:** Överväg att kräva pre-escaped content eller lägg till wp_kses_post som default om det är HTML från användare

### 4. Inline styles

**inc/admin-meta-boxes/hooks.php** (rad 46), **inc/admin-meta-boxes/service.php** (rad 19):
- `echo '<style>...'` – inline style för admin-specifika tweaks
- **Kommentar:** Style guide säger "inga inline styles" – men för admin UI-hiding är det vanligt. Överväg att flytta till CSS-fil.

### 5. PHPDoc – täckning ✓ (genomfört)

- **Åtgärd:** Lägg till PHPDoc på alla publika funktioner
- **Genomfört:** @param/@return tillagd på: route-stations.php, route-destinations.php, service-save.php, stoptimes.php, timetable-services.php, journey.php, timetable-frontend.php

---

## Filöversikt (urval)

| Fil/Område | Ansvar |
|------------|--------|
| museum-railway-timetable.php | Huvudfil, plugin bootstrap |
| inc/constants.php | MRT_TEXT_DOMAIN, post types, taxonomier |
| inc/assets.php | CSS/JS enqueue, localization |
| inc/cpt.php | Custom post types |
| inc/admin-ajax/*.php | AJAX-handlers (stoptimes, journey, timetable, route-destinations, route-stations) |
| inc/admin-meta-boxes/*.php | Meta boxes för station, route, timetable, service |
| inc/functions/*.php | Helpers (services, stations, routes, connections, datetime) |
| inc/shortcodes/*.php | shortcode-month, shortcode-journey, shortcode-overview |
| inc/admin-page/*.php | Dashboard, stats, routes, shortcodes |

---

## Sammanfattning

- **Struktur och grund:** Bra – följer STYLE_GUIDE och WordPress-konventioner
- **Prioritet 1–5 genomförda:** Sanitize nonce, $_GET, MRT_render_info_box (wp_kses_post), inline styles → CSS, PHPDoc-täckning
