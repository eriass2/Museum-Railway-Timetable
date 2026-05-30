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
| `GET|POST /timetables/{id}/deviations` | Avvikelser |
| `GET /timetables/{id}/overview` | Overview-JSON (samma som idag) |
| `GET|PUT /settings/prices` | Prismatris |
| `POST /import/csv`, `GET /export/csv` | Import/export |
| `POST /journey/search` | Resesökning (publikt) |
| `GET /journey/calendar` | Trafikdagar (publikt) |
| `GET /journey/connections/{id}` | Detalj (publikt) |

Exakta paths och payloads dokumenteras i [ADMIN_VUE_PLAN.md](ADMIN_VUE_PLAN.md) per fas.

---

## Migration från AJAX (nuvarande läge)

Allt under `inc/infrastructure/ajax/` ska flyttas till REST och sedan raderas.

| AJAX action (idag) | REST-ersättare (plan) | Område |
|--------------------|------------------------|--------|
| `mrt_search_journey` | `POST /journey/search` | Publikt |
| `mrt_journey_calendar_month` | `GET /journey/calendar` | Publikt |
| `mrt_journey_connection_detail` | `GET /journey/connections/{id}` | Publikt |
| `mrt_get_timetable_for_date` | `GET /timetables/for-date` | Publikt |
| `mrt_timetable_overview_data` | `GET /timetables/{id}/overview` | Publikt + admin |
| `mrt_*_stoptime*` | `PUT /services/{id}/stop-times` | Admin |
| `mrt_add_service_to_timetable` | `POST /timetables/{id}/services` | Admin |
| `mrt_remove_service_from_timetable` | `DELETE /timetables/{id}/services/{sid}` | Admin |
| `mrt_get_route_destinations` | `GET /routes/{id}/destinations` | Admin |
| `mrt_get_route_stations_for_stoptimes` | `GET /routes/{id}/stations` | Admin |
| `mrt_save_route_end_stations` | `PATCH /routes/{id}` | Admin |

**Klientkod att migrera:**

- `frontend/vue/src/api/mrtApi.ts` → `mrtRest.ts` (eller liknande)
- `assets/admin-*.js` → ersätts av Vue admin (tas bort)
- `inc/assets/admin.php` / `frontend.php` — sluta lokalisera `ajaxurl`

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
