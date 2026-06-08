# Tillgänglighet (WCAG 2.1 AA)

Mål där det är tekniskt rimligt utan att duplicera temats ansvar. **Manuell rökning** vid release eller större tema-/CSS-ändringar.

**Relaterat:** [SHORTCODES.md](SHORTCODES.md), [ARCHITECTURE.md](ARCHITECTURE.md), [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md).

---

## Snabb automatisk kontroll (kod)

Kör före manuell rökning:

```powershell
php scripts/validate.php
```

Valideringen inkluderar statiska a11y-markörer i publika moduler (region, aria-live, wizard steg).

**Miljö:** http://localhost:8080 — **Component demo** (månad, översikt, wizard) eller **Wizard smoke test**. Login: `admin` / `admin`.

---

## Manuell checklista (ca 15–30 min)

### Wizard `[museum_journey_wizard]`

- [ ] Tab genom steg 1→4; synlig fokus på knappar/länkar/select
- [ ] Steglista: aktivt steg hörs/ser (`aria-current="step"`)
- [ ] Kalender: dagknappar med begripligt `aria-label`; `aria-pressed` byts vid val
- [ ] Felmeddelande läses upp (`role="alert"`)
- [ ] Resultatkort / tabell: `caption` + `scope="col"` där tabell används
- [ ] Tågtypsikoner stämmer (ång, diesel, rälsbuss, buss) per ben
- [ ] Zoom 200 % – inget horisontellt klipp i hero/panel (embedded-läge på demo)

### Månad `[museum_timetable_month]`

- [ ] Månadsnav: länkar föregående/nästa med tydlig text
- [ ] Trafikdag: `aria-pressed` på aktiv dag
- [ ] Panel under kalender uppdateras utan att förlora kontext

### Översikt `[museum_timetable_overview]`

- [ ] Varje rutt: region + rubrik (`h3`)
- [ ] Tidsceller har beskrivande `aria-label` (station + tåg + tid)
- [ ] Tågtypsikoner syns i rutnätet

### Tidtabellsindex `[museum_timetable_index]`

- [ ] Minst ett kort synligt; länk/tab fungerar
- [ ] Introtext och färgmarkör (grön/gul/röd/orange) syns

### Admin

- [ ] Railway Timetable → dashboard: `MrtButton` / WP `.button` har synlig `:focus-visible`
- [ ] Import / demo / clear: formulär kan tabbas; fel/notice läsbara

### Miljö

- [ ] Samma tema/plugins som produktion; kort NVDA eller VoiceOver på wizard + en shortcode till
- [ ] `.po` uppdaterade om nya strängar lagts till (Poedit eller `msgfmt`)

---

## Release-logg

| Datum | Testare | Resultat | Anteckning |
|-------|---------|----------|------------|
| | | | |

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
