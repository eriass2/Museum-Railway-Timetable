# REST API – policy och migration

Museum Railway Timetable ska använda **WordPress REST API** för all klient–server-kommunikation. **`admin-ajax.php` ska fasas ut helt** — inget i pluginet ska anropa eller registrera AJAX-actions i slutläget.

---

## Beslut

| Beslut | Värde |
|--------|--------|
| Transport | WordPress REST API (`/wp-json/…`) |
| AJAX | **Förbjudet i slutläge** — befintliga `wp_ajax_*` tas bort när REST-ersättare finns |
| Publikt frontend (Vue) | REST + cookie/nonce där användaren är inloggad; publika läs-endpoints utan auth |
| Admin (Vue) | REST + `X-WP-Nonce` (`wp_rest`) + capability checks |
| Domänlogik | Oförändrat i `inc/domain/` — REST-controllers är tunna adapters |

---

## Namespace och konventioner

- **Namespace:** `museum-railway-timetable/v1` (URL: `/wp-json/museum-railway-timetable/v1/…`)
- **Registrering:** `inc/infrastructure/rest/` (en fil per resursområde)
- **Metoder:** GET för läsning, POST/PATCH/PUT för skrivning, DELETE där det passar
- **Svar:** JSON; fel via `WP_Error` med HTTP-status (400 validering, 403 capability, 404 saknas, 500 oväntat)
- **Validering:** Domänfunktioner i PHP; controllers sanerar input och mappar till domän
- **Säkerhet:** `permission_callback` per route; aldrig lita på klientvalidering ensam

### Exempel på resurser (målbild)

| Route (prefix `/museum-railway-timetable/v1`) | Syfte |
|-----------------------------------------------|--------|
| `GET /dashboard` | Status, varningar, snabbstatistik |
| `GET|POST /stations`, `GET|PATCH /stations/{id}` | Stationer |
| `GET|POST /routes`, `GET|PATCH /routes/{id}` | Rutter + stationordning |
| `GET|POST /timetables`, `GET|PATCH /timetables/{id}` | Tidtabeller |
| `GET|POST /timetables/{id}/services` | Turer |
| `GET|PUT /services/{id}/stop-times` | Stopptider |
| `PUT /services/{id}/departure` | Snabb avgångstid (mobil) |
| `POST /dev/*` | Dev-verktyg (endast dev-läge): clear-db, import, demo-page, setup-navigation, sync-timetable-pages |
| `GET|POST /timetables/{id}/deviations` | Avvikelser |
| `GET /timetables/{id}/overview` | Overview-JSON (samma som idag) |
| `GET|PUT /settings/prices` | Prismatris |
| `POST /import/csv`, `GET /export/csv` | Import/export |
| `POST /journey/search` | Resesökning (publikt) |
| `GET /journey/calendar` | Trafikdagar (publikt) |
| `GET /journey/connections/{id}` | Detalj (publikt) |

Exakta paths och payloads dokumenteras i [ADMIN_VUE_PLAN.md](ADMIN_VUE_PLAN.md) per fas.

---

## Migration från AJAX

**Status (2026-05):** AJAX-lagret är borttaget. Publikt Vue och admin använder REST under `museum-railway-timetable/v1`.

| Tidigare AJAX action | REST |
|--------------------|------|
| `mrt_search_journey` | `POST /journey/search` |
| `mrt_journey_calendar_month` | `POST /journey/calendar` |
| `mrt_journey_connection_detail` | `POST /journey/connection-detail` |
| `mrt_get_timetable_for_date` | `GET /timetables/day?date=&train_type=` |
| `mrt_timetable_overview_data` | `GET /timetables/{id}/overview` |
| Admin stopptider/turer/rutter | Se tabellen ovan (admin routes) |
| Tågtyper | `GET|POST /train-types`, `PATCH|DELETE /train-types/{id}` |
| CSV | `POST /import/csv`, `GET /export/csv` |

**Klient:** `frontend/vue/src/api/mrtRest.ts` (publikt); `frontend/vue/src/admin/api/adminRest.ts` (admin).

Publika routes kräver `X-WP-Nonce` (`wp_rest`) i frontend-config (`restUrl` + `restNonce`).

---

## Definition of done (per endpoint)

- [ ] Route registrerad med `permission_callback` och input schema
- [ ] Domänlogik anropas från befintlig `MRT_*`-funktion (ingen duplicering)
- [ ] PHPUnit-test för controller + domän där det är meningsfullt
- [ ] Vue/klient använder REST
- [ ] Ingen kvarvarande referens till motsvarande AJAX action
- [ ] `grep` på action-namnet i repo returnerar tomt

---

## Förbjudet i ny kod

- `add_action( 'wp_ajax_*' )` / `wp_ajax_nopriv_*`
- `admin_url( 'admin-ajax.php' )` i enqueue/localize
- `fetch( ajaxurl, { action: 'mrt_*' } )` i JS/TS

Tillfälligt undantag under migration: befintliga handlers får leva tills REST-ersättaren är i produktion och tester är gröna.

---

## Referenser

- [ADMIN_VUE_PLAN.md](ADMIN_VUE_PLAN.md) — Vue-admin och faser
- [ARCHITECTURE.md](ARCHITECTURE.md) — lager och adapters
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
