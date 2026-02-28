# JavaScript – Granskning mot Style Guide

Granskning av `assets/*.js` mot STYLE_GUIDE.md och COMPONENT_LIBRARY.md.

**Senast åtgärdat:** XSS-fix, felhantering, i18n, Clean Code-refaktorering genomförd.

---

## ✅ Följs

| Krav | Status |
|------|--------|
| **IIFE** | Alla filer wrappas i `(function($) { ... })(jQuery)` |
| **jQuery** | Använder `$` för DOM |
| **console.log** | Endast bakom `window.mrtDebug` (admin.js, admin-timetable-services-ui.js) |
| **camelCase** | Variabler och funktioner använder camelCase |
| **Prefix** | `mrtAdmin`, `mrtFrontend` för lokaliserade strängar |
| **Nonces** | Alla AJAX-anrop skickar nonce |
| **Felhantering** | De flesta AJAX-anrop har success/error-hantering |
| **CSS-klasser** | Använder `.mrt-*` enligt COMPONENT_LIBRARY |

---

## ⚠️ Förbättringar

### 1. XSS-säkerhet
**admin-service-edit.js** (rad 131–134) och **admin-route-ui.js** (rad 38–39):
- `station.name` och `stationName` sätts direkt i HTML-strängar
- **Åtgärd:** Använd `document.createElement` + `textContent` eller escape-funktion

### 2. Saknad felhantering
**admin-route-ui.js** (rad 116–138): AJAX för end stations har ingen `error`-callback.
- **Åtgärd:** Lägg till `error: function() { ... }` och visa meddelande vid nätverksfel

### 3. Hårdkodade strängar (i18n)
Strängar som borde komma från `mrtAdmin`/`mrtFrontend`:
- `admin-service-edit.js`: "Saving...", "Leave empty if train stops...", "Pickup", "Dropoff"
- `admin-timetable-services-ui.js`: "Edit", "Trip added successfully.", "Trip removed successfully."
- **Åtgärd:** Lägg till i `wp_localize_script` och använd i JS

### 4. showError – XSS
**frontend.js** (rad 81): `$container.html('<div class="...">' + message + '</div>')`
- Om `message` kommer från API kan den vara escaped på servern
- **Åtgärd:** Använd `textContent` eller escape innan insättning i HTML

### 5. Långa funktioner (Clean Code: max 50 rader)
- **admin-service-edit.js** `bindSaveAllStoptimes`: ~55 rader
- **admin-stoptimes-ui.js** `initStopTimesUI`: ~220 rader
- **admin-timetable-services-ui.js** `initTimetableServicesUI`: ~210 rader
- **Åtgärd:** Dela upp i mindre funktioner (t.ex. `buildSaveStoptimesHandler`, `handleSaveSuccess`)

---

## Namnkonventioner

| Nuvarande | Style guide | Kommentar |
|-----------|--------------|-----------|
| `MRTAdminUtils` | mrtAdmin, mrtFrontend | OK – objektnamn, inte lokaliserings-objekt |
| `MRTAdminServiceEdit` | – | OK – modul/namespace |

---

## Sammanfattning

- **Struktur och grund:** Bra
- **Prioritet 1:** XSS-säkerhet (station.name, message i HTML)
- **Prioritet 2:** Felhantering för route end stations
- **Prioritet 3:** i18n för hårdkodade strängar
- **Prioritet 4:** Refaktorera långa funktioner
