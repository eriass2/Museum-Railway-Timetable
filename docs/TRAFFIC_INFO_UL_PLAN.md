# Trafikinfo UL 1:1 — implementeringsplan

**Status:** plan (ej startad) — **backlog:** [TODO.md](TODO.md) § Trafikinfo UL 1:1 (`TF-*`)  
**Kodbaseline:** 2026-06-14 — se §16 (inga TF-implementationer utom token-filer + förberedande wizard-UI)  
**Mål:** Publik trafikstörningsvy ska **visuellt** matcha UL:s «Aktuellt trafikläge» / «Planerade avvikelser» (hierarki, färger, badges, alert-rutor). Innehåll för Lennakatten (tågnummer, museumstrafik).  
**Kontext:** [TRAFFIC_DISRUPTIONS_PLAN.md](TRAFFIC_DISRUPTIONS_PLAN.md) (v2 API/feed klar 2026-06-11) — denna plan är **uppföljning för visuell paritet**.  
**Designreferens:** UL-skärmdumpar (team), [mockups/TRAFFIC_INFO_TOKENS.md](mockups/TRAFFIC_INFO_TOKENS.md)

---

## 1. UL-mönster (målbild)

```
┌─ Aktuellt trafikläge ─── (klock-ikon, ljusgrå rubrik) ─────────────┐
│  [Buss/Tåg]  (i 17)  (! 2)  [+]     ← kategori-rad, gul vid aktiv   │
│    [71]  Meddelande i korall-ruta                    [+]              │
│    🕐 Gäller 14 juni kl. 10:41 t.o.m. kl. 11:15                       │
└──────────────────────────────────────────────────────────────────────┘
┌─ Planerade avvikelser ─ (kalender-ikon) ────────────────────────────┐
│  …                                                                    │
└──────────────────────────────────────────────────────────────────────┘
```

**Tre nivåer:** sektion → kategori (med count-badges) → alert-kort.  
**Inte:** flat lista, grön vänsterkant, dubbelt datum i rubrik, separat rad «Mer information».

---

## 2. Ansvar (vem gör vad)

| Roll | Person / team | Ansvar |
|------|---------------|--------|
| **Produkt** | Jesper + operatör | UL-referens OK, svenska rubriker, manuell acceptans mot skärmdump |
| **Design / tokens** | Implementerare + produkt | Färger, spacing, ikoner — **en källa** ([TRAFFIC_INFO_TOKENS.md](mockups/TRAFFIC_INFO_TOKENS.md), `assets/mrt-traffic-info-tokens.css`) |
| **Backend / domän** | PHP | Payload, gruppering, textfält (`summary`, `validity_label`), counts — **ingen HTML/CSS** |
| **REST + shortcode** | PHP | JSON till Vue; noscript med **samma BEM-klasser**, inga inline styles |
| **Vue trafikinfo** | Frontend | Komponentträd, expand-state, scoped CSS + tokens |
| **Delade UI-primitiver** | Frontend | Ikoner, badges (`MrtTfIcon*`, count-badge, line-badge) — domänfria |
| **Admin** | Frontend + PHP l10n | Förhandsvisning = samma Vue-komponenter; valfria nya fält |
| **Test** | PHP + Vitest + Playwright | API-kontrakt, e2e, visual regression |

### Gräns: data vs presentation

| Lager | Gör | Gör inte |
|-------|-----|----------|
| **PHP** | `panels`, `categories`, `counts`, färdiga strängar för giltighet | `style=`, HTML-layout, färgklasser per item |
| **Vue** | BEM `.mrt-tf-*`, färger via `--mrt-tf-*` | `:style`, datum-strängar i template, inline hex |
| **Noscript** | Samma klasser som Vue | Inline styling |

---

## 3. CSS-arkitektur (ingen inline styling)

### Förbjudet

- `style="…"` i Vue eller PHP
- Hex/rgb hårdkodat i komponent-`<style>` (utan token)
- Wizard-tokens (`--mrt-wizard-*`) som surrogate för trafikinfo

### Tillåtet stack

```
docs/mockups/TRAFFIC_INFO_TOKENS.md     Human spec + UL-referens
assets/mrt-traffic-info-tokens.css      :root --mrt-tf-* (enda källa för färger)
frontend/vue/src/components/traffic-notices/
  traffic-info-layout.css               BEM layout (flex/grid), inga färger
  MrtTf*.vue                            scoped + @import tokens
```

Ikoner: SVG med `currentColor` (samma mönster som `WizardTripTypeIcon.vue`).

### Definition of Done (styling)

- [ ] `grep -r 'style=' frontend/vue/src/components/traffic-notices/` → tomt
- [ ] Noscript i `shortcode.php` → inga `style=`
- [ ] Alla UL-färger definierade i `mrt-traffic-info-tokens.css`
- [ ] Admin preview och publik vy delar samma komponenter

---

## 4. API-kontrakt (utökning)

Bakåtkompatibilitet: befintliga `ongoing`, `upcoming`, `items` kvar tills noscript/admin migrerat.

### Nya fält per item

| Fält | Typ | Ansvar | Användning i UI |
|------|-----|--------|-----------------|
| `summary` | string | PHP | Text i korall-ruta (utan datum) |
| `validity_label` | string | PHP | Rad under rutan: «Gäller …» |
| `line_label` | string | PHP | Svart tågnummer-badge |
| `severity` | `info` \| `warning` | PHP | **i** vs **!** badge |
| `category_key` | string | PHP | Gruppering |
| `category_label` | string | PHP | «Tåg», «Buss», «Information» |
| `icon_key` | string | PHP | `steam` \| `diesel` \| `railbus` \| `bus` |

`headline` — legacy; UI använder `summary` + `validity_label`.

### Severity

| `kind` | `severity` | UL-badge |
|--------|------------|----------|
| `info` | `info` | **i** (gul) |
| `deviation` | `warning` | **!** (röd) |
| `cancelled` | `warning` | **!** (röd) |

### Kategori (från befintlig data)

| Källa | `category_key` | `category_label` |
|-------|----------------|------------------|
| Avvikelse, `train_type` ≠ buss | `train` | Tåg |
| Avvikelse, slug `buss` | `bus` | Buss |
| Generellt meddelande | `general` | Information |

### Ny top-level: `panels`

```typescript
type DisruptionFeedPanel = {
  key: 'ongoing' | 'upcoming';
  title: string;              // "Aktuellt trafikläge" | "Planerade avvikelser"
  icon: 'clock' | 'calendar';
  categories: DisruptionFeedCategory[];
};

type DisruptionFeedCategory = {
  key: string;
  label: string;
  icon_key: string;
  counts: { info: number; warning: number };
  items: DisruptionFeedItem[];
};
```

PHP: `MRT_disruption_feed_build_panels()` i `disruption-feed.php`; presentation i `disruption-feed-display.php`.

### Valfria schemaändringar (fas B)

| Fält | Var | För |
|------|-----|-----|
| `valid_from_time`, `valid_to_time` | Trafikmeddelanden (option) | «kl. 10:41 t.o.m. kl. 11:15» |
| `category` | Trafikmeddelanden | Tvinga kategori utan tågkoppling |
| `headline` | Trafikmeddelanden | Fri rubrik separat från `text` |

Utan tider: `validity_label` med datumintervall — dokumenterad skillnad mot UL.

---

## 5. Vue-komponentträd

**Ansvar:** Frontend (trafikinfo). **Primitiver:** Delad UI.

```
MrtTrafficNoticesView.vue
└── MrtTfPanels.vue
    └── MrtTfPanel.vue                    sektion + rubrik-ikon
        └── MrtTfCategoryRow.vue          kategori, badges, expand
            └── MrtTfAlertList.vue
                └── MrtTfAlertCard.vue    line-badge + korall + giltighet + [+]
```

**Deprecate / ta bort efter migrering:**

- `MrtDisruptionFeedSections.vue` (flat sektion)
- `MrtDisruptionFeedItemCard.vue` (grön kant, dubbel expand)
- `MrtExpandTrigger` «Mer information» i trafikinfo

**Nya primitiver** (`components/ui/` eller `traffic-notices/`):

- `MrtTfCountBadge.vue` — pill **i** / **!** + siffra
- `MrtTfLineBadge.vue` — svart nummer
- `MrtTfIconClock.vue`, `MrtTfIconCalendar.vue`, `MrtTfIconWarning.vue`
- Återanvänd `MrtInfoMark.vue` endast om `currentColor` + `--mrt-tf-info` räcker; UL count-pill troligen **`MrtTfCountBadge`** (se §16)

**State:** `expandedCategoryKey` per panel; valfritt `expandedAlertId` för detalj under kort.

---

## 6. Faser och leveranser

| Fas | Primär | Leverans | Beräknad insats |
|-----|--------|----------|-----------------|
| **0 Tokens** | Design → Frontend | `TRAFFIC_INFO_TOKENS.md`, `mrt-traffic-info-tokens.css`, UL-referens i `mockups/` | ½ dag |
| **A Data** | Backend | `summary`, `validity_label`, `severity`, `line_label`, category-fält; fix dubbel datum i headline | 1 dag |
| **B API panels** | Backend | `panels[]` + counts; TS-typer | 1 dag |
| **C Vue** | Frontend | Komponentträd + `traffic-info-layout.css` | 2–3 dagar |
| **D Noscript** | Backend | `shortcode.php` samma BEM | ½ dag |
| **E Admin** | Admin FE | `TrafficNoticesFeedPreview` → nya komponenter | ½ dag |
| **F Test** | Test | PHPUnit, Vitest, Playwright screenshot | 1 dag |
| **G Acceptans** | Produkt | Sida vid sida med UL-referens | — |

**Valfritt (fas B schema):** tidsfält i admin — Backend + Admin, +1 dag.

**Rekommenderad ordning:** 0 → A → B+C (parallellt möjligt) → D → E → F → G.

---

## 7. Filkarta

| Fil | Ansvar |
|-----|--------|
| `docs/mockups/TRAFFIC_INFO_TOKENS.md` | Design |
| `docs/mockups/ul-trafikinfo-reference.png` | Produkt |
| `assets/mrt-traffic-info-tokens.css` | Frontend |
| `inc/assets/vue-mount-layout.php` | Backend — `alignwide` på `traffic_notices` mount |
| `inc/assets/vue-frontend.php` | Backend — Vue enqueue; hook för token-CSS efter bundle |
| `inc/assets/brand-tokens.php` | Backend — mönster för CSS efter Vue (operatör-brand) |
| `inc/domain/traffic-notices/disruption-feed.php` | Backend |
| `inc/domain/traffic-notices/disruption-feed-display.php` | Backend |
| `inc/public/traffic-notices/shortcode.php` | Backend |
| `inc/public/vue-shortcode-config.php` | Backend (i18n) |
| `frontend/vue/src/api/disruptionFeed.ts` | Frontend |
| `frontend/vue/src/apps/TrafficNoticesApp.vue` | Frontend |
| `frontend/vue/src/components/layout/MrtPublicAppShell.vue` | Frontend — wrapper publik vy |
| `frontend/vue/src/components/traffic-notices/` | Frontend — **fortfarande** `MrtDisruption*` (ej `MrtTf*`) |
| `frontend/vue/src/components/ui/MrtInfoMark.vue` | Delad UI — wizard/timeline (ej trafikinfo än) |
| `tests/Unit/DisruptionFeedTest.php` | Test |
| `frontend/vue/tests/disruptionFeedDisplay.test.ts` | Test |
| `frontend/vue/e2e/traffic-notices-mount.spec.ts` | Test |

---

## 8. i18n

| Nu | Mål (UL) |
|----|----------|
| Pågår nu | Aktuellt trafikläge |
| Kommande | Planerade avvikelser |
| Mer information | Bort — innehåll synligt eller `+` |
| — | Gäller %s (validity mall) |

Text domain: `museum-railway-timetable`.

---

## 9. Acceptanskriterier (1:1 visuellt)

- [ ] Två paneler med klock-/kalender-ikon och UL-rubriker
- [ ] Kategori-rad med **i**/**!**-badges före expand
- [ ] Alert: linjebadge + korall-ruta + giltighetsrad med klock-ikon
- [ ] Ingen «Mer information»-rad; en expand-nivå (kategori + valfritt alert `+`)
- [ ] Ingen dubbel datumtext
- [ ] Inga inline styles; tokens i CSS-fil
- [ ] Noscript och Vue visuellt likvärdiga

---

## 10. Risker

| Risk | Hantering |
|------|-----------|
| UL har klockslag; vi har oftast datum | Datum-only `validity_label`; valfria tidsfält i admin |
| Fler tågtyper än Buss/Tåg | Kategori «Tåg»; undertyp via `icon_key` / `line_label` |
| Bakåtkompatibilitet | Legacy arrays tills noscript migrerat |

---

## 11. Relation till andra dokument

| Dokument | Roll |
|----------|------|
| [TRAFFIC_DISRUPTIONS_PLAN.md](TRAFFIC_DISRUPTIONS_PLAN.md) | v2 beslut, gap v1, fas 1–4 historik |
| [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md) | v1 shortcode, datakällor |
| [mockups/TRAFFIC_INFO_TOKENS.md](mockups/TRAFFIC_INFO_TOKENS.md) | Visuella tokens |
| [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md) | Uppdatera med `MrtTf*` efter implementering |

---

## 12. Slutoutput — vad som avgör kvaliteten (utöver layout)

Layout och tokens räcker inte om **innehåll, fixtures och verifiering** inte följer samma modell. Dessa punkter påverkar det du/Jesper faktiskt ser på test3.

### 12.1 Innehåll och API-text (största synliga skillnaden idag)

| Problem idag | Effekt i UI | Åtgärd | Ansvar |
|--------------|-------------|--------|--------|
| `headline` innehåller datum **och** `date_label` visas | «Idag – 2026-09-03 … – Idag – 2026-09-03» | Fas A: `summary` utan datum; `validity_label` separat | Backend |
| Generellt meddelande = första raden i `text` | Rubrik kan bli rörig eller för lång | Admin: valfri `headline`; operatörguide | Backend + Produkt |
| Avvikelse-headline = «Inställd — Tåg 71 — Faringe – Uppsala» | UL visar **meddelande** i korall, nummer i badge | `summary` = händelsetext; `line_label` = 71 | Backend |
| Datum som `2026-09-03` | UL: «14 juni» | `MRT_disruption_feed_validity_label()` med svensk månad | Backend |
| E2E/test3 «E2E notice …» | Ser inte UL-lik i demo | UL-lik fixture + Lennakatten demo | Test + Backend |

**Operatörguide** (nytt, kort): i admin-hjälp — *«Första raden blir meddelande i den orange rutan. Datum visas automatiskt under.»* — **Produkt** text, **Admin** i `admin-help/traffic-notices.php`.

### 12.2 Referensdata (fixtures som speglar målbilden)

| Fixture | Nu | Mål |
|---------|-----|-----|
| `e2e/fixtures/traffic-notices-payload.mjs` | 2 flat items, `upcoming: []` | `panels` med Tåg (i+!), Buss, `upcoming` med planerad rad |
| `traffic-demo-data.php` | Bra texter (baninfo, glassrea) men flat API | Samma texter via nya fält efter Fas A |
| `docs/mockups/ul-trafikinfo-reference.png` | Saknas i repo | Produkt lägger UL + vår målbild sida vid sida |

E2E ska testa **UL-flödet** (expand kategori → alert synlig), inte bara «Glassrean» i flat lista.

### 12.3 CSS når faktiskt användaren

| Kanal | Krav | Ansvar |
|-------|------|--------|
| Vue (JS) | `mrt-traffic-info-tokens.css` importerad i trafikinfo-chunk **eller** enqueue efter Vue-CSS (samma hook som `MRT_enqueue_brand_token_overrides` i `vue-frontend.php`) | Frontend + Backend |
| Noscript | Samma token-fil enqueue när shortcode renderas utan JS | Backend (`frontend.php` / shortcode) |
| WordPress-tema | `alignwide` via `MRT_vue_mount_extra_classes()` i `vue-mount-layout.php` + `MrtPublicAppShell` — verifiera på Lennakatten-tema | Test + Produkt |

Utan enqueue av tokens ser noscript-användare och vissa WP-miljöer **fel färger** trots korrekt HTML.

### 12.4 Visuell verifiering (låser 1:1 över tid)

| Verktyg | Innehåll | Ansvar |
|---------|----------|--------|
| Playwright `toHaveScreenshot` | `traffic-notices-ul-desktop.png`, mobil 390px — som `wizard-timeline-layout.spec.ts` | Test |
| Side-by-side doc | Sektion i `TRAFFIC_INFO_TOKENS.md`: UL vs vår snapshot | Design |
| Jesper-checklista | 8 punkter, skriv under på test3 (nedan §14) | Produkt |

Pixel-perfect mot UL är inte målet — **samma hierarki, färger och typografi** är.

### 12.5 Edge cases som ofta glöms

| Scenario | UL-lik beteende | Ansvar |
|----------|-----------------|--------|
| Tom `ongoing`, data i `upcoming` | Visa bara «Planerade avvikelser»-panel | Vue |
| Bara generella meddelanden (ingen avvikelse) | Kategori «Information», inga !-badges | Backend gruppering |
| Många avvikelser samma dag | Count-badges (i 4), inte 4 identiska rader utan gruppering | Backend + Vue |
| Inställd + info samma kategori | Båda badges (i + !) | Backend counts |
| Mobil | Datum/giltighet bryts till egen rad | Frontend CSS `@media` |
| `details` noscript | Samma BEM som Vue, inte legacy `mrt-traffic-notices__feed-item` | Backend shortcode |

---

## 13. Fas H — slutpolish (lägg till i ordning efter F)

| Steg | Primär | Leverans |
|------|--------|----------|
| **H1** | Backend | Svenskt datum i `validity_label` |
| **H2** | Test | UL-lik `traffic-notices-payload.mjs` + uppdaterade e2e |
| **H3** | Backend | Enqueue `mrt-traffic-info-tokens.css` (shortcode + hook som Vue efter bundle) |
| **H4** | Test | Playwright screenshots |
| **H5** | Produkt | Operatörguide + Jesper-checklista §14 |
| **H6** | Produkt | test3 på Lennakatten-tema |

---

## 14. Jesper acceptanschecklista (manuell)

Kör på `/trafikstorningar` (eller shortcode på startsidan), mobil + desktop.

1. Rubriker: «Aktuellt trafikläge» och «Planerade avvikelser» med ikon.
2. Kategori-rad (Tåg/Buss) med **i**- och vid behov **!**-siffra **innan** expand.
3. Gul markering på expanderad kategori.
4. Tågnummer i **svart badge**, inte i löptext.
5. Meddelande i **korallfärgad ruta** — läsbar utan att expandera.
6. «Gäller …» på egen rad under, med klocka — **inte** dubbelt datum i samma rad.
7. Inga rader «Mer information»; expand med **+** som UL.
8. Jämför med UL-app/webb: «känns samma nivå av överblick».

---

## 15. Uppdaterad fasöversikt

| Fas | Fokus |
|-----|--------|
| 0 | Tokens |
| A | Data (`summary`, `validity_label`) |
| B | API `panels` |
| C | Vue `MrtTf*` |
| D | Noscript BEM |
| E | Admin preview |
| F | PHPUnit + Vitest + e2e funktion |
| **H** | **Fixtures, enqueue CSS, screenshots, operatör, tema** |
| G | Produkt acceptans §14 |

**Kritiskt för slutoutput:** A + TF-F5 + TF-H1 — utan dem ser test3 fortfarande «fel» även med perfekt CSS.

---

## 16. Kodstatus (baseline 2026-06-14)

Jämförelse mellan plan och faktisk kod. **TF-ul-arbetet är i stort sett ej påbörjat**; nedan är relevanta förändringar i närheten.

### Redan i kod (förberedelse — inte UL-feed klart)

| Vad | Var | Påverkan på TF-plan |
|-----|-----|---------------------|
| Token-spec + `--mrt-tf-*` CSS-fil | `TRAFFIC_INFO_TOKENS.md`, `assets/mrt-traffic-info-tokens.css` | TF-0.1 ✅ TF-0.2 ✅ fil, **ej enqueue/import** |
| `MrtInfoMark.vue` (grön cirkel-i, `#fff` i SVG) | `components/ui/` | Används i **wizard** tidslinje + fotnoter — **inte** trafikinfo. TF-C5: behöver troligen **egen** `MrtTfCountBadge` (gul **i** i grå pill), inte återanvända rakt av |
| Timeline-modul refactor | `components/ui/timeline/*` | Parallellt arbete; **ej** trafikinfo |
| `MrtPublicAppShell` | `TrafficNoticesApp.vue` | TF-H2: layout-wrapper finns; tema-verifiering kvar |
| `MRT_vue_mount_extra_classes()` | `inc/assets/vue-mount-layout.php` | `traffic_notices` får `alignwide` (flyttad från `vue-frontend.php`) |
| `MRT_enqueue_brand_token_overrides()` | `vue-frontend.php` + `brand-tokens.php` | TF-H1: **mönster** för att enqueue CSS efter Vue-bundle — trafikinfo-tokens ej kopplade |

### Oförändrat sedan plan (fortfarande öppna TF-punkter)

| Område | Nuvarande kod |
|--------|----------------|
| API-fält | Inga `summary`, `validity_label`, `severity`, `panels` — `disruptionFeed.ts` + `disruption-feed.php` |
| Vue trafikinfo | `MrtDisruptionFeedSections`, `MrtDisruptionFeedItemCard` — **ingen** `MrtTf*` |
| i18n rubriker | Fortfarande «Pågår nu» / «Kommande» i `vue-shortcode-config.php` |
| Noscript | Legacy `mrt-traffic-notices__*` + `<details>` i `shortcode.php` |
| E2E fixture | Flat `ongoing`/`upcoming` i `traffic-notices-payload.mjs` |
| CSS DoD | Inga `mrt-tf-*` klasser i produktion |

### Planjusteringar (efter kodgranskning)

1. **TF-H1:** enqueue via ny `MRT_enqueue_traffic_info_tokens( $after_handle )` (lik `brand-tokens.php`) **eller** `@import` i trafikinfo Vue-entry — inte bara «frontend.php» generiskt.
2. **TF-H2:** referensfil `vue-mount-layout.php`, inte endast `vue-frontend.php`.
3. **TF-C5:** `MrtInfoMark` är **wizard**-ikon (brand-grön); UL count-badge är annorlunda — planera `MrtTfCountBadge` som primär, `MrtInfoMark` som valfri inre ikon med `currentColor` från `--mrt-tf-info`.
4. **Strict tokens:** `MrtInfoMark` har hårdkodad `#fff` i SVG — bryter TF CSS-regler; nya TF-komponenter ska använda `currentColor` / tokens.

### Slutsats

**Planen behöver inte ändras i faser eller scope** — kodbasen matchar «ej startad» för TF-A–G. Uppdateringar ovan är **precision** (filer, enqueue-mönster, MrtInfoMark ≠ UL-badge). Nästa implementationsteg förblir **TF-A** (+ TF-F5, TF-H1 för synligt test3-resultat).

