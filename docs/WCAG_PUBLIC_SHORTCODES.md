# WCAG – publika shortcodes (resa + månad)

Mål: **WCAG 2.1 nivå AA** där det är tekniskt rimligt utan att duplicera temats ansvar. Detta dokument täcker **`[museum_journey_planner]`** och **`[museum_timetable_month]`** (PHP + `assets/frontend.js` + månads-CSS).

**Relaterat:** [WCAG_JOURNEY_WIZARD.md](WCAG_JOURNEY_WIZARD.md), [SHORTCODES_OVERVIEW.md](SHORTCODES_OVERVIEW.md), [ARCHITECTURE.md](ARCHITECTURE.md).

---

## 1. `[museum_journey_planner]`

| Område | Åtgärd |
|--------|--------|
| **Landmärken** | Yttre omslutning `role="region"` med `aria-label` (Journey planner). Resultat: `role="region"`, `aria-live="polite"`, `aria-relevant="additions text"`, `aria-busy` styrs under AJAX-sökning. |
| **Tabell** | Delad markup i `inc/functions/journey-connections-table.php`: `<caption>`, `scope="col"`, tider formaterade konsekvent. Retursökning (AJAX): särskild caption-text (`Return train connections…`). |
| **Rubrik** | `h3` med `id="mrt-journey-results-heading"` för fokus efter sök; JS flyttar fokus och tar bort tillfällig `tabindex` vid blur. |
| **Meddelanden** | `MRT_render_alert`: `role="alert"` för fel/varning, `role="status"` för info. Inline alert-rutor i resultat samma mönster. |
| **JS** | `aria-busy` på sökknapp och resultatregion; fel från nät/API behandlas likadant för fokus. |

**Filer:** `inc/shortcodes/shortcode-journey.php`, `inc/admin-ajax/journey-render.php`, `inc/functions/journey-connections-table.php`, `assets/frontend.js`, `assets/admin-timetable-table.css` (caption-stil).

---

## 2. `[museum_timetable_month]`

| Område | Åtgärd |
|--------|--------|
| **Landmärken** | Kalenderwrapper `role="region"` med beskrivande `aria-label` (månad + år). Panel under kalendern för vald dags tidtabell: `role="region"`, `aria-live="polite"`, `aria-busy`, `tabindex="-1"`, `aria-label`; fokus efter AJAX-laddning. |
| **Navigation** | `role="navigation"` med `aria-label` (Month navigation). |
| **Tabell** | `<caption>` (Operating days for …), veckodagar `scope="col"`. |
| **Dagar med trafik** | `<button type="button">` med `aria-label` (datum + ev. antal turer), `aria-pressed` uppdateras i JS; visuella siffror/markör `aria-hidden` där etiketten är fullständig. |
| **Dagar utan trafik** | Cell med datum siffra, ingen knapp. |
| **Legend** | Dekorativ färgpunkt `aria-hidden="true"`. |
| **Rörelse / fokus** | `prefers-reduced-motion`: ingen scale-hover på dagknappar; `:focus-visible` på knappar; slideDown för panel kan kringgås vid reduced motion i JS. |

**Filer:** `inc/shortcodes/shortcode-month.php`, `assets/frontend.js`, `assets/admin-timetable-month.css`.

---

## 3. Manuell checklista (vid release eller temaändring)

- [ ] **Planner:** tabba formulär → sök → resultat; skärmläsare läser caption och kolumnrubriker; efter AJAX hamnar fokus logiskt.
- [ ] **Månad:** tabba prev/next → veckodagar → en trafikdag (knapp) → panel uppdateras; `aria-pressed` följer vald dag.
- [ ] Zoom **200 %**: kalenderceller och tabeller fortfarande användbara.
- [ ] Kontrast mot aktivt **tema** (gröna celler / blå aktiv dag).

---

## 4. Kända gränser

- Samma som wizard: **tema** och **manuell** skärmläsarverifiering per miljö.
- **`[museum_timetable_overview]`** och andra shortcodes har **egen** granskning om de utökas.

---

## 5. Referenser

- [WCAG 2.1](https://www.w3.org/TR/WCAG21/)
- [WAI-ARIA Practices](https://www.w3.org/WAI/ARIA/apg/)
