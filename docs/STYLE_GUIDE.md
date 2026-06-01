# Style Guide – Museum Railway Timetable

Kodstandarder och clean code-principer för projektet (PHP, CSS, JS, WordPress).

**Arkitektur och rebuild-design:** [REBUILD_RULES.md](REBUILD_RULES.md) och [ARCHITECTURE.md](ARCHITECTURE.md). Denna guide fokuserar på namngivning, säkerhet och filkonventioner så att regler inte dupliceras.

**Visuell design (färger):** [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md) — tokens i `assets/mrt-color-tokens.css`. Mockups i `docs/mockups/` är arkiverad referens.

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
- **Nonces** – Formulär och REST-anrop ska använda nonces (`X-WP-Nonce` / `wp_rest`)
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
- **Färgpalett** – Se [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md); implementera via `assets/mrt-color-tokens.css` (`--mrt-color-*`, wizard-alias `--mrt-wizard-*`).
- **UI-klasser** – Nya klasser ska använda `.mrt-*` och vara BEM-liknande där det behövs.
- **CSS-variabler** – Använd tokens från paletten; undvik nya hårdkodade hex-värden i komponenter.
- **Mobile-first** – Basstilar för mobil, `@media (min-width)` för större skärmar.
- **Inga inline styles** – All styling i CSS-filer.

### Publik UI (wizard m.fl.)
- **Primär accent:** `--mrt-color-accent-600` (`#DDD24C`) — Lennakatten varumärkesguld för CTA, aktivt steg och vald restyp.
- **Text på guld:** `--mrt-color-on-accent` (**svart**), enligt [grafisk profil](https://lennakatten.se/grafisk-profil/).
- **Vue-bundle:** Publik CSS ligger under `frontend/vue/src/styles/` och byggs till `assets/dist/vue/`. Entry: `mrt-public.css` (tokens + delade primitives); appar importerar egna moduler (`journey-wizard.css`, `timetable-overview.css`). Efter ändring: `npm run build` i `frontend/vue/` och committa `assets/dist/vue/`.
- **Wizard-CSS:** `frontend/vue/src/styles/journey-wizard/` — `base.css`, `wizard-shell.css`, `controls-form.css` (sök steg), `controls-calendar.css`, `steps-*.css`, `responsive.css`. Importeras från `JourneyWizardApp.vue`.
- **Tidtabellsöversikt-CSS:** `frontend/vue/src/styles/timetable-overview.css` — block `.mrt-ov-*`, importeras från `MrtTimetableOverviewView.vue`. Använd tokens (`--mrt-color-green-*`, `--mrt-from-to-bg`, `--mrt-transfer-*` från `assets/frontend/tokens.css`) i stället för nya hex-värden.
- **Färgtokens:** `assets/mrt-color-tokens.css` importeras först i `mrt-public.css`. Se [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md) och [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md).
- **Restyp-ikoner:** SVG i `WizardTripTypeIcon.vue`; stylas med `currentColor` i `controls-form.css` (scoped under `.mrt-journey-wizard .mrt-surface`).

### Exempel
```html
<button class="mrt-button mrt-button--primary">Spara</button>
<div class="mrt-card">...</div>
<div class="mrt-form-field">...</div>
```

---

## 4. JavaScript

### Struktur
- **Vue + TypeScript** – Publikt frontend och admin i `frontend/vue/` (Vite → `assets/dist/vue/`)
- **Ingen jQuery-frontend** – DOM och state hanteras i Vue-komponenter
- **Ingen `console.log`** i produktion – endast med debug-flagga

### Namnkonventioner
- **camelCase** för variabler och funktioner
- **Prefix** för plugin-specifika: `mrtAdmin`, `mrtFrontend` etc.

### Event och REST
- **Nonces** – Skicka `X-WP-Nonce` (restNonce) på REST-anrop
- **Felhantering** – Hantera nätverksfel och visa användarvänliga meddelanden

### Delade util-moduler
- **Datum/tid och strängar** – Vue-utils under `frontend/vue/src/` (t.ex. `wizardDate.ts`, `mrtStrings.ts`); testas med Vitest (`npm test` i `frontend/vue/`).

### Admin (Vue)
- **Vue-admin** – `frontend/vue/src/admin/` byggt till `assets/dist/vue/assets/admin.js` (`vite.admin.config.ts`).
- **REST** – `adminRest.ts` mot `inc/infrastructure/rest/`; nonce via `mrtAdminVue`.
- **CSS** – `assets/admin.css` (WP-native skal) + `admin-shell.css` i Vue-bundeln.

### Publikt frontend (Vue)
- **Ingen jQuery-frontend** – månad, översikt och wizard mountar Vue (`frontend/vue/`, byggt till `assets/dist/vue/`).
- **REST** – Vue anropar `wp-json/museum-railway-timetable/v1/` med nonce från mount-config.
- **CSS** – Vite-bundel; källfiler under `frontend/vue/src/styles/` (se §3 CSS).

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
- **Loader-filer** – Tunna loaders (`inc/infrastructure/rest/loader.php`, `inc/admin/meta-boxes.php`) require:ar undermappar

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
│   ├── infrastructure/            # CPT, rest/, wordpress/
│   ├── admin/                     # dashboard/, meta-boxes/, tools/
│   ├── public/                    # month-calendar, timetable-overview, journey-wizard
│   ├── import/lennakatten/
│   ├── assets/                    # enqueue-hjälpare
│   ├── admin.php, shortcodes.php, assets.php
│   └── constants.php
├── assets/
│   ├── admin.css                  # WP-admin skal (Vue-admin)
│   ├── dist/vue/                  # Vite bundle (public + admin JS/CSS)
│   └── icons/train-types/
├── frontend/vue/src/styles/       # Vue-ägd publik CSS (se §3 CSS)
│   ├── mrt-public.css             # tokens + assets primitives + vue-shell
│   ├── journey-wizard/            # wizard-moduler
│   └── timetable-overview.css     # .mrt-ov-* tidtabell
└── languages/
```

---

## 7. Contributing – Snabbchecklista

- [ ] Följer WordPress coding standards
- [ ] PHPDoc på alla nya funktioner
- [ ] All output escaped
- [ ] All input sanitized
- [ ] Nonces på REST-anrop (X-WP-Nonce)
- [ ] Inga inline styles
- [ ] CSS-klasser med `.mrt-` prefix
- [ ] Funktioner med `MRT_` prefix
- [ ] Översättningsfunktioner med text domain
- [ ] Testerat manuellt

---

## 8. Referenser

- **REBUILD_RULES.md** – Rebuild-regler för kod, design och kvalitet
- **VUE_UI_COMPONENTS.md** – Vue-komponenter, tokens och alerts
- **design/COLOR_PALETTE.md** – Färgpalett och kontrast
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Clean Code (Robert C. Martin)](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882)
