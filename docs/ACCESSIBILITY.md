# Tillgänglighet (WCAG 2.1 AA)

Mål där det är tekniskt rimligt utan att duplicera temats ansvar. **Manuell rökning** vid release eller större tema-/CSS-ändringar.

**Relaterat:** [SHORTCODES.md](SHORTCODES.md), [ARCHITECTURE.md](ARCHITECTURE.md), [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md) (manuell logg).

---

## Release-rökning (ca 15–30 min)

- [ ] **`[museum_journey_wizard]`** – Tabba igenom steg; kalender och fel; zoom 200 %; tågtypsikoner i resultat.
- [ ] **`[museum_timetable_month]`** – Månadsnav; trafikdag med `aria-pressed`; panel under kalendern.
- [ ] **`[museum_timetable_overview]`** – Region per rutt; `h3` + beskrivande namn på tidsceller i rutnät.
- [ ] **`[museum_timetable_index]`** – Listan renderas (Vue); kort/länkar tabbbara; färgmarkör synlig.
- [ ] **Admin** – Synlig `:focus-visible` på `MrtButton` / WP `.button` (t.ex. dashboard snabbstart).
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
| Fokus | Vid stegbyte fokus på rubrik (`tabindex="-1"`, tas bort vid blur); `:focus-visible` på knappar (se `frontend/vue/src/styles/journey-wizard.css`) |
| Rörelse | `prefers-reduced-motion` i wizard base CSS (samma kedja) |
| Ikoner | Dekorativa `<img alt="">` i resekort; tidtabell i drawer använder samma PNG som översikt |

**Filer:** `inc/public/journey-wizard/` (config), `frontend/vue/src/wizard/components/`, `frontend/vue/src/styles/journey-wizard.css`.

---

## Månad och översikt

**Månad:** kalender-region; dagknappar med `aria-pressed`; panel `aria-busy` vid laddning. Filer: `inc/public/month-calendar/`, `frontend/vue/src/apps/MonthCalendarApp.vue`.

**Översikt:** Vue-komponenter med `role="region"`, `h3` per rutt, semantisk tabell för branch-bussar. Filer: `inc/domain/timetable/view/overview-data.php`, `frontend/vue/src/components/overview/`.

**Tidtabellsindex:** Vue-lista med `nav` + länkkort; `aria-label` per kort (titel + meta). Filer: `inc/public/timetable-index/`, `frontend/vue/src/apps/TimetableIndexApp.vue`.

---

## Gränser

- Sidvis kontrast mot produktionstema krävs fortfarande.
- Tredjepartstema kan åsidosätta plugin-CSS.
- [WCAG 2.1](https://www.w3.org/TR/WCAG21/) · [WAI-ARIA APG](https://www.w3.org/WAI/ARIA/apg/)
