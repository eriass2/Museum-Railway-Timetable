# Tillgänglighet (WCAG 2.1 AA)

Mål där det är tekniskt rimligt utan att duplicera temats ansvar. **Manuell rökning** vid release eller större tema-/CSS-ändringar.

**Relaterat:** [SHORTCODES.md](SHORTCODES.md), [ARCHITECTURE.md](ARCHITECTURE.md).

---

## Release-rökning (ca 15–30 min)

- [ ] **`[museum_journey_wizard]`** – Tabba igenom steg; kalender och fel; zoom 200 %.
- [ ] **`[museum_journey_planner]`** – Sök resa; fokus efter AJAX på resultatrubrik; tabell-caption i skärmläsare.
- [ ] **`[museum_timetable_month]`** – Månadsnav; trafikdag med `aria-pressed`; panel under kalendern.
- [ ] **`[museum_timetable_overview]`** – Region per rutt; `h3` + beskrivande namn på tidsceller i rutnät.
- [ ] **Admin** – Synlig `:focus-visible` på minst en sida med `mrt-btn`.
- [ ] **Miljö** – Samma tema/plugins som produktion; kort NVDA/VoiceOver-session.
- [ ] **Översättningar** – Uppdatera `languages/*.po`, kompilera `.mo` (Poedit eller `msgfmt`).

---

## Wizard (`[museum_journey_wizard]`)

| Område | Åtgärd |
|--------|--------|
| Landmärken | Varje steg `role="region"` + `aria-labelledby` |
| Steglista | `<nav aria-label>`; aktivt steg `aria-current="step"` |
| Fel | `role="alert"`, `aria-live="assertive"` |
| Kalender | Region, `aria-busy`, dagknappar med `aria-label` + `aria-pressed` |
| Tabeller | `caption`, `scope="col"`; dold Actions-kolumn med `.mrt-sr-only` |
| Fokus | Vid stegbyte fokus på rubrik (`tabindex="-1"`, tas bort vid blur) |
| Rörelse | `prefers-reduced-motion: reduce` i `journey-wizard.css` |

**Filer:** `shortcode-journey-wizard.php`, `journey-wizard.js`, `journey-wizard.css`.

---

## Planner, månad, översikt

**Planner:** region + `aria-live` på resultat; tabell via `journey-connections-table.php`; `frontend.js`.

**Månad:** kalender-region; dagknappar med `aria-pressed`; panel `aria-busy` vid AJAX. Filer: `shortcode-month.php`, `frontend.js`.

**Översikt:** CSS Grid (inte `<table>`) – `role="region"`, `h3` per rutt, `aria-label` på celler via `MRT_overview_grid_cell_aria_label`. Filer: `timetable-view/overview.php`, `grid*.php`.

---

## Gränser

- Sidvis kontrast mot produktionstema krävs fortfarande.
- Tredjepartstema kan åsidosätta plugin-CSS.
- [WCAG 2.1](https://www.w3.org/TR/WCAG21/) · [WAI-ARIA APG](https://www.w3.org/WAI/ARIA/apg/)
