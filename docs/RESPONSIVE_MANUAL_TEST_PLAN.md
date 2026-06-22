# Testplan — responsivitet (F0 + T1–T8)

**Relaterat:** [RESPONSIVE_AUDIT_PLAN.md](RESPONSIVE_AUDIT_PLAN.md), [RESPONSIVE_AUDIT_RESULTS.md](RESPONSIVE_AUDIT_RESULTS.md), [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md)

Manuell rökning efter ändringar i layout, tokens, admin mobil eller publika shells. Kör **automatiskt först**, sedan manuellt där E2E inte täcker WP-tema/inbäddning.

---

## 1. Förberedelse (Docker)

```powershell
# Full dev-miljö (WordPress + demo-data)
.\scripts\mrt.ps1 dev reset

# Vue gate (bygger admin.js + publik bundle)
.\scripts\mrt.ps1 vue-check
```

| Detalj | Värde |
|--------|--------|
| Webb | http://localhost:8080 |
| Admin | http://localhost:8080/wp-admin/admin.php?page=mrt_app |
| Login | `admin` / `admin` |
| Komponentdemo | http://localhost:8080/museum-railway-timetable-component-demo/ |

**DevTools:** device toolbar → **390×820** (primär mobil, samma som E2E) och **1920×1080** (max-width-cap). Valfritt: **320×568** (minsta stöd).

---

## 2. Automatiserad gate (Docker Playwright)

Statiska mounts (ingen WP krävs för dessa):

```powershell
# Alla responsiva specs (~8 filer)
.\scripts\mrt.ps1 e2e -- --grep "responsive"

# En yta i taget
.\scripts\mrt.ps1 e2e -- wizard-responsive.spec.ts
.\scripts\mrt.ps1 e2e -- overview-responsive.spec.ts
.\scripts\mrt.ps1 e2e -- month-responsive.spec.ts
.\scripts\mrt.ps1 e2e -- traffic-notices-responsive.spec.ts
.\scripts\mrt.ps1 e2e -- index-responsive.spec.ts
.\scripts\mrt.ps1 e2e -- admin-responsive.spec.ts
.\scripts\mrt.ps1 e2e -- admin-timetable-editor-responsive.spec.ts
.\scripts\mrt.ps1 e2e -- admin-prices-import-responsive.spec.ts
```

**Förväntat:** alla gröna. Kräver färsk `vue-check` (E2E läser `assets/dist/vue/assets/admin.js`).

**WP-integration** (befintlig CI-suite, inte alla responsive-specs):

```powershell
.\scripts\mrt.ps1 dev e2ewp
```

---

## 3. Manuell checklista per yta

Markera ✅ när ok. **Fail** = horisontell sid-scroll (utom avsiktlig inre scroll), klippt text, eller klickyta &lt; ~44px.

### Publik (komponentdemo eller shortcode-sida)

| ID | Yta | URL / mount | Mobil 390px | Desktop 1920px |
|----|-----|-------------|-------------|----------------|
| T1 | Reseplanerare | Demo → wizard | Steg 1–4 utan sid-overflow; steg-nav scrollar internt vid behov | Innehåll cap (~64rem wizard / 80rem shell) |
| T2 | Tidtabellsöversikt | Demo → overview | Rutnät scrollar i `.mrt-ov-grid-scroll`, inte hela sidan | Bred layout inom app-shell |
| T3 | Månadskalender | Demo → month | Kalender utan sid-overflow; nav-knappar tryckbara | Centrerad, läsbar |
| T4 | Trafikinfo | `/traffic-notices` (static) eller demo | Feed inom panel; kategorirader ≥44px | Feed cap (~36rem) |
| T5 | Tidtabellsindex | Demo → index | Kort full bredd, inga klippta titlar | Cap ~42rem (`--mrt-max-content`) |

**T1 djupdyk (valfritt):** tur-retur, detaljpanel, pris-tabell, sammanfattning — samma viewport.

### Admin mobil (782px-breakpoint ≈ viewport ≤782px; testa 390px)

| ID | Yta | Route / nav | Kontrollera |
|----|-----|-------------|-------------|
| T6 | Shell + dashboard | `#/` (Översikt) | Nav ovanför innehåll; inga sid-scroll; nav-länkar ≥44px höjd |
| T7 | Tidtabellseditor | `#/timetables/1` | Snabb avgångstid: select + tid fält fullbredd; spara-knapp fullbredd; avvikelse-form utan smal max-width |
| T8 | Priser | `#/prices` | Ingen sid-overflow; pris-matris scrollar i tabell-container; schema-tabell staplad |
| T8 | Stationer | `#/stations-routes` | Stationlista staplad (kort-layout); inga sid-scroll |
| T8 | Import | `#/import-export` | Export-alternativ (checkboxar) staplade vertikalt |

**Admin desktop (1280px):** dashboard inline stats; tabeller kan vara breda med horisontell scroll i `.admin-table-scroll` — acceptabelt.

---

## 4. Snabb visuell rökning (5 min)

1. **390px** — bläddra komponentdemo: wizard → overview → month → index.
2. **390px** — admin: Översikt → Tidtabeller (mobil editor) → Priser → Stationer → Import.
3. **1920px** — wizard + index: innehåll ska inte bli orimligt brett (tomma marginaler, cap synlig).
4. Hård refresh (Ctrl+F5) om CSS verkar gammal.

---

## 5. Kända avsiktliga undantag

| Beteende | Var | OK? |
|----------|-----|-----|
| Horisontell scroll **inuti** grid/tabell-container | Overview, pris-matris | ✅ |
| Admin desktop-tabeller bredare än fönster | Priser, rutter | ✅ (scroll i container) |
| Wizard steg-progress scroll | Reseplanerare mobil | ✅ |

---

## 6. Release-minimum

Innan merge/deploy efter responsivitetsändring:

- [ ] `.\scripts\mrt.ps1 vue-check`
- [ ] `.\scripts\mrt.ps1 e2e -- --grep "responsive"`
- [ ] Manuell §4 (eller full §3 vid större CSS-refactor)
- [ ] Vid WP-tema/inbäddning: extra check på riktig shortcode-sida (inte bara demo)

---

## 7. Felsökning

| Symptom | Åtgärd |
|---------|--------|
| Admin-E2E hittar inte CSS-fix | Kör `vue-check` (bygger om `admin.js`) |
| Playwright öppnar fel app på Windows | Använd `.\scripts\mrt.ps1 e2e`, inte `vendor/bin/phpunit` eller rå `docker compose run composer …` |
| WP-E2E fail efter manuell test | `.\scripts\mrt.ps1 dev reset` eller `dev e2ewp` (restore) |
| Gammal bundle i webbläsare | Ctrl+F5; vid dev reset — kontrollera att `assets/dist/vue/` uppdaterats |
