# Delat UI-bibliotek (Vue)

Plan för ett gemensamt komponentbibliotek som både **publikt frontend** och **Vue-admin** kan använda.

**Relaterat:** [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md), [design/CSS_REFACTOR_PLAN.md](design/CSS_REFACTOR_PLAN.md), [STYLE_GUIDE.md](STYLE_GUIDE.md) §3.

---

## Mål

1. **En implementation** per UI-mönster (alert, loading, knapp) — inte parallella `Mrt*` + `Admin*`.
2. **Två visuella kontexter** via prop (`context="public" | "admin"`), inte duplicerad markup.
3. **CSS colocation** i SFC där det går; tokens fortsatt i `assets/mrt-color-tokens.css`.
4. **Gradvis migration** — admin-specifika wrappers behålls tills all kod flyttats.

---

## Lager

```
assets/mrt-color-tokens.css          ← designsystem (delat)
frontend/vue/src/components/
├── ui/                              ← delade primitiver (detta bibliotek)
│   ├── MrtAlert.vue
│   ├── MrtAsyncState.vue
│   ├── MrtButton.vue
│   ├── MrtDot.vue
│   └── index.ts
├── overview/                        ← domän (redan delat admin + publik)
└── admin/components/ui/             ← admin-specifikt (tabeller, editor, …)
```

| Lager | Exempel | Delas? |
|-------|---------|--------|
| Tokens | `--mrt-color-green-700` | Ja |
| Primitiver | Alert, knapp, spinner | Ja (`context`-prop) |
| Domän | `MrtTimetableOverviewView` | Ja (redan idag) |
| App-skals | wizard-hero, admin-sidebar | Nej |
| Admin-only | `AdminTableScroll`, `StopTimesEditor` | Nej |

---

## `context`-prop

Alla delade primitiver accepterar `context`:

| Värde | Var | Utseende |
|-------|-----|----------|
| `public` (default) | Shortcodes, wizard | Lennakatten — `.mrt-ui-alert`, `.mrt-accent-btn` |
| `admin` | wp-admin Vue-app | WordPress — `.notice`, `.button` |

```vue
<MrtButton context="public" variant="primary">Sök resa</MrtButton>
<MrtButton context="admin" variant="primary">Spara</MrtButton>
```

---

## Komponentkarta

### Fas 1 — påbörjad ✓

| Komponent | Publik | Admin | Status |
|-----------|--------|-------|--------|
| `MrtAlert` | `MrtAlert` | via `AdminStatusMessage` | `context` tillagd |
| `MrtAsyncState` | wizard, overview | via `AdminLoadState` | `context` + retry |
| `MrtButton` | ersätter `MrtAccentButton` | ny — gradvis migration | skapad |
| `MrtDot` | legend, kalender | — | skapad |

### Fas 2 — påbörjad ✓

| Åtgärd | Beskrivning | Status |
|--------|-------------|--------|
| Migrera admin-knappar | `MrtButton context="admin"` i alla admin-sidor | ✓ |
| Colocate CSS | Flytta `assets/frontend/ui/alerts.css` m.fl. till SFC | planerad |
| Ta bort död CSS | `.mrt-btn`, `.mrt-badge`, `.mrt-form-*` | planerad |
| `AdminPanel` → `MrtPanel` | valfritt | planerad |

### Fas 3 — större

| Åtgärd | Beskrivning |
|--------|-------------|
| `[museum_timetable_index]` → Vue | sista PHP-UI:t |
| Overview-CSS | behåll globalt (komplex grid) |

---

## Import-konventioner

**Publikt** — importera från `@/components/ui`:

```ts
import { MrtAlert, MrtButton, MrtAsyncState } from '@/components/ui';
```

**Admin** — använd befintliga `Admin*`-exports tills migration är klar; wrappers delegerar till delade primitiver:

```ts
import { AdminLoadState, AdminStatusMessage } from '../components/ui';
// internt: MrtAsyncState context="admin", MrtAlert context="admin"
```

Nya admin-sidor kan importera direkt från `@/components/ui`.

---

## CSS-regler efter införande

1. **Nya primitiver** — styling i SFC (`<style scoped>`), inte nya filer i `assets/frontend/ui/`.
2. **Tokens** — alltid `@import` / `var(--mrt-*)` från `mrt-color-tokens.css`.
3. **App-specifikt** — `frontend/vue/src/styles/<app>/`.
4. **Admin-skals** — `admin/styles/admin-shell.css` (layout, tabeller).

---

## Bakåtkompatibilitet

| Gammalt | Nytt | Strategi |
|---------|------|----------|
| `MrtAccentButton` | `MrtButton context="public"` | `MrtAccentButton` delegerar till `MrtButton` |
| `AdminLoadState` | `MrtAsyncState context="admin"` | Wrapper kvar i admin/ui |
| `AdminStatusMessage` | `MrtAlert context="admin"` | Wrapper kvar i admin/ui |
| `assets/frontend/ui/*.css` | SFC scoped | Ta bort modul när colocated |

---

## Checklista per ny primitiv

- [ ] Props: `context`, variants dokumenterade i denna fil + `VUE_UI_COMPONENTS.md`
- [ ] Används i minst ett publikt + ett admin-flöde (eller motiverat undantag)
- [ ] Tokens — inga nya hårdkodade hex
- [ ] `:focus-visible` testad (admin + publik)
- [ ] Vitest om klass-logik är icke-trivial
