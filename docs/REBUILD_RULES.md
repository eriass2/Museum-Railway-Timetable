# Rebuild rules – code, design and quality

Regler för utveckling av Museum Railway Timetable.

---

## 1. Produktregler

- Bygg bara funktioner som stöder [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md).
- Referens-PDF:er och mockups styr beteende och UI-prioritet.
- Varje ny feature ska kunna demonstreras med demo/testdata.
- Admin ska göra datahantering enkel; frontend ska visa resenärsflödet.

---

## 2. Arkitekturregler

### Domän först

Domänlogik ska ligga i rena PHP-funktioner utan HTML, `$_POST`, `$_GET` eller echo.

Exempel:

- datumval
- trafikdagar
- sökning av resor
- byten
- priser
- import-normalisering

### Tunna adapters

WordPress-lager ska bara:

1. läsa input
2. verifiera capability/nonce
3. sanera input
4. anropa domänfunktion
5. returnera HTML/JSON eller spara data

### Presentation

Presentation får rendera HTML men ska inte innehålla affärsregler. JS får hålla UI-state men servern är sanning för:

- tillgängliga datum
- giltiga resor
- priser
- stopptider
- tågtyp

---

## 3. Kodstandard

- Max 50 rader per funktion. Sikta på 20–35 rader.
- En funktion gör en sak.
- Namn ska beskriva domänbeteende, inte implementation.
- Undvik anonyma långa callbacks; namnge dem.
- Undvik fil > 300 rader där det finns naturliga moduler.
- En loader-fil ska bara require:a moduler.
- Ny domänlogik ska ha enhetstest.
- Nya renderhelpers ska testas när de har villkor/logik.

---

## 4. PHP-regler

- Prefix `MRT_` för funktioner.
- Prefix `mrt_` för hooks, meta keys, post types och taxonomier.
- Alla PHP-filer utom specialfall ska ha `ABSPATH` guard.
- Escape all output med `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
- Sanera input med rätt WordPress-funktion.
- Alla admin actions ska ha capability check och nonce.
- SQL ska använda `$wpdb->prepare()` för parametrar.
- Inga inline styles utom kontrollerade CSS-variabler där validatorn tillåter det.

---

## 5. JavaScript-regler

- Använd IIFE eller tydlig modulgräns.
- Dela återanvändbar logik i `mrt-*.js`.
- Ingen `console.log` i produktion utom bakom `window.mrtDebug`.
- JS ska inte duplicera sök-/pris-/datumregler från PHP.
- Klient–server ska gå via **WordPress REST API** — se [REST_API.md](REST_API.md). Ingen ny `admin-ajax.php` / `wp_ajax_*`.
- Under migration: befintlig AJAX får leva tills REST-ersättare finns; ny kod ska bara använda REST.
- UI-rendering ska delas i små namngivna helpers.

---

## 6. CSS/designregler

### Admin

- Admin ska vara WordPress-native.
- Använd WordPress-klasser där de räcker: `wrap`, `button`, `button-primary`, `button-secondary`, `notice`, `widefat`, `form-table`.
- Egen admin-CSS ska bara användas när WordPress saknar stöd för layouten.
- Undvik stora specialdesignade admin-kort om standard-WP räcker.

### Frontend

- Frontend ska följa mockupens prioriterade flöde.
- CSS ska delas i moduler när filen passerar cirka 300 rader.
- Använd `mrt-` prefix.
- Använd CSS-variabler för färg, spacing och semantiska tokens.
- Tågikonerna i `assets/icons/train-types/` ska behållas som produktassets om inte ny design uttryckligen ersätter dem.
- Behåll tillgänglig fokusindikering.
- Responsivt beteende ska vara mobile-first.

---

## 7. Testregler

Minimikrav per PR:

- `composer test`
- `composer phpstan`
- `composer phpcs`
- `composer plugin-check`
- `npm test` (i `frontend/vue/` — Vitest)

UI-ändringar ska dessutom verifieras i WordPress med screenshot eller video.

Importändringar ska verifieras mot referensdata och minst ett importtest.

---

## 8. Cleanup-regler

Innan radering:

1. Beskriv varför filen inte stöder målbilden.
2. Kontrollera om den innehåller data, regler eller tester som ska flyttas.
3. Radera bara i en PR där scope är tydligt.

Spara alltid:

- referens-PDF:er
- mockups
- relevanta tester
- dokument som beskriver rebuild-skissen och rebuild-reglerna

---

## 9. Definition of done

En rebuild-del är klar när:

- den är testad automatiskt
- den är verifierad manuellt om UI påverkas
- den följer fil-/funktionslängdregler
- den har tydliga namn
- den kan förklaras från mockup, tidtabell eller produktmål
