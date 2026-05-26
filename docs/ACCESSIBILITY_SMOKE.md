# Tillgänglighet – manuell rökning (logg)

Använd tillsammans med [ACCESSIBILITY.md](ACCESSIBILITY.md). Kryssa av efter genomgång i Docker-demo (`admin` / `admin`).

**Miljö:** http://localhost:8080 — demosida med alla shortcodes.

---

## Snabb automatisk kontroll (kod)

Kör före manuell rökning:

```powershell
php scripts/validate.php
```

Valideringen inkluderar statiska a11y-markörer i publika moduler (region, aria-live, wizard steg).

---

## Checklista

### Wizard `[museum_journey_wizard]`

- [ ] Tab genom steg 1→5; synlig fokus på knappar/länkar/select
- [ ] Steglista: aktivt steg hörs/ser (`aria-current="step"`)
- [ ] Kalender: dagknappar med begripligt `aria-label`; `aria-pressed` byts vid val
- [ ] Felmeddelande läses upp (`role="alert"`)
- [ ] Resultattabell: `caption` + `scope="col"` där tabell används
- [ ] Zoom 200 % – inget horisontellt klipp i hero/panel

### Planner `[museum_journey_planner]`

- [ ] Region märkt ”Journey planner”
- [ ] Efter sök: fokus/announce i resultatregion (`aria-live="polite"`)
- [ ] Tabell har caption i skärmläsare

### Månad `[museum_timetable_month]`

- [ ] Månadsnav: länkar föregående/nästa med tydlig text
- [ ] Trafikdag: `aria-pressed` på aktiv dag
- [ ] Panel under kalender uppdateras utan att förlora kontext

### Översikt `[museum_timetable_overview]`

- [ ] Varje rutt: region + rubrik (`h3`)
- [ ] Tidsceller har beskrivande `aria-label` (station + tåg + tid)

### Admin

- [ ] Railway Timetable → dashboard: `.mrt-btn` har synlig `:focus-visible`
- [ ] Import / demo / clear: formulär kan tabbas; fel/notice läsbara

### Miljö

- [ ] Kort NVDA eller VoiceOver på wizard + en shortcode till
- [ ] `.po` uppdaterade om nya strängar lagts till

---

## Logg

| Datum | Testare | Resultat | Anteckning |
|-------|---------|----------|------------|
| | | | |
