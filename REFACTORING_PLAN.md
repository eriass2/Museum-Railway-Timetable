# Plan: Bryta ner långa filer

Översikt över filstorlekar och föreslagen uppdelning för enklare utveckling.

**Max metodlängd:** 50 rader (STYLE_GUIDE.md + .cursor/rules)

## Nuvarande storlekar (uppskattat)

| Fil | Rader | Status |
|-----|-------|--------|
| inc/admin-page.php | ~517 | ✅ Uppdelad (dashboard, clear-db) |
| inc/admin-meta-boxes.php | ~1514 | ✅ Uppdelad |
| assets/admin.css | ~1796 | ✅ Uppdelad (base, timetable, meta-boxes, dashboard, ui, responsive) |
| assets/admin.js | ~1057 | ✅ Uppdelad (utils, route-ui, stoptimes-ui, timetable-services-ui) |
| inc/admin-ajax.php | ~781 | ✅ Uppdelad |
| inc/functions/helpers.php | ~710 | ✅ Uppdelad |
| inc/functions/timetable-view.php | ~420 | ✅ Uppdelad (prepare, grid, overview) |

## 1. admin-meta-boxes.php → inc/admin-meta-boxes/

**Uppdelning:**
- `admin-meta-boxes.php` – Loader som require:ar alla moduler
- `station.php` – Station meta box + save
- `route.php` – Route meta box + save
- `timetable.php` – Timetable meta box + save
- `timetable-services.php` – Timetable services (trips) box
- `timetable-overview.php` – Timetable overview preview
- `service.php` – Service meta box + save
- `service-stoptimes.php` – Stop times box + MRT_render_stoptime_row
- `hooks.php` – Delade hooks (init, block editor, etc.)

## 2. admin-ajax.php → inc/admin-ajax/

**Uppdelning:**
- `admin-ajax.php` – Loader + register
- `stoptimes.php` – add, update, delete, get, save_all
- `timetable-services.php` – add_service, remove_service
- `route-destinations.php` – get_route_destinations
- `route-stations.php` – get_route_stations_for_stoptimes, save_route_end_stations
- `journey.php` – search_journey
- `timetable-frontend.php` – get_timetable_for_date

## 3. helpers.php → inc/functions/

**Uppdelning:**
- `helpers.php` – Loader
- `helpers-stations.php` – get_station_display_name, get_all_stations
- `helpers-routes.php` – route-station, direction, label
- `helpers-services.php` – get_service_train_type, get_service_destination, get_service_stop_times
- `helpers-datetime.php` – validate_date, validate_time, get_current_datetime
- `helpers-connections.php` – find_connecting_services

## 4. admin.js → assets/

**Alternativ A: Flera moduler (enqueue per modul)**
- `admin.js` – Init + utilities
- `admin-route-ui.js` – Route/destination dropdowns
- `admin-stoptimes-ui.js` – Stop times inline editing
- `admin-timetable-services-ui.js` – Timetable services

**Alternativ B: Behåll en fil, men tydliga sektioner**
- Lägg till `// === SECTION: X ===` kommentarer för bättre navigering

## 5. admin-page.php → inc/admin-page/

**Uppdelning (klar):**
- `admin-page.php` – Loader (menu, settings registration)
- `dashboard.php` – MRT_render_admin_page, settings field callbacks
- `clear-db.php` – Clear DB handler (WP_DEBUG only)

## 6. admin.css → assets/

**Uppdelning (klar):**
- `admin-base.css` – Variables, base
- `admin-timetable.css` – Table, month calendar, legend
- `admin-timetable-overview.css` – Grid, cells, overview
- `admin-meta-boxes.css` – Meta box styles
- `admin-dashboard.css` – Dashboard, stats, form elements
- `admin-ui.css` – Status, loading, messages, journey planner
- `admin-responsive.css` – Media queries

**Loader:** inc/assets.php enqueue:ar alla CSS-filer
