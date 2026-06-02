# Delat UI-bibliotek (Vue) — masterplan

Gemensamt komponentbibliotek för **publikt frontend** och **Vue-admin**.

**Relaterat:** [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md), [design/CSS_REFACTOR_PLAN.md](design/CSS_REFACTOR_PLAN.md), [STYLE_GUIDE.md](STYLE_GUIDE.md) §3.

---

## Översikt

| Fas | Innehåll | Status |
|-----|----------|--------|
| **1** | Primitiver + `context`-prop | ✓ Klar |
| **2** | Admin-knappar, CSS colocate, städa död CSS | ✓ Klar |
| **3** | `[museum_timetable_index]` → Vue | ✓ Klar |
| **4** | Dokumentation + underhållsregler | ✓ Klar |

---

## Fas 1 — Primitiver ✓

| Komponent | Publik | Admin |
|-----------|--------|-------|
| `MrtButton` | `context="public"` → `.mrt-accent-btn` | `context="admin"` → WP `.button` |
| `MrtAlert` | `.mrt-ui-alert` | WP `.notice` |
| `MrtAsyncState` | loading / error / empty | + `@retry` |
| `MrtDot` | legend, kalender | — |
| `MrtAccentButton` | wrapper → `MrtButton` | — |

Admin wrappers (behålls): `AdminLoadState`, `AdminStatusMessage`.

---

## Fas 2 — CSS & admin ✓

| Steg | Beskrivning | Status |
|------|-------------|--------|
| 2a | Alla admin-knappar → `MrtButton context="admin"` | ✓ |
| 2b | Colocate: `MrtAlert`, `MrtButton`, `MrtDot`, `MrtSurfaceCard` (scoped SFC) | ✓ |
| 2c | Ta bort oanvänd CSS: `.mrt-btn`, `.mrt-badge`, `.mrt-info-box`, `.mrt-form-*` (utom `.mrt-input` i admin) | ✓ |
| 2d | Rensa `assets/frontend/ui/alerts.css`, `surface-buttons.css` från barrel | ✓ |
| 2e | `AdminPanel` → `MrtPanel` | **Skippad** — låg vinst, admin-specifik layout |

---

## Fas 3 — Sista PHP-UI:t ✓

| Steg | Beskrivning |
|------|-------------|
| 3a | `TimetableIndexApp.vue` + `MrtTimetableIndexView.vue` |
| 3b | Shortcode mountar Vue (`app: index`) med items i config |
| 3c | CSS: `frontend/vue/src/styles/timetable-index.css` |
| 3d | Ta bort separat PHP-enqueue för index |
| 3e | Behåll `MRT_render_timetable_index_html()` för PHPUnit |

**Medvetet kvar som global CSS:** `timetable-overview.css`, wizard-shell, `.mrt-empty` (månadskalender).

---

## Fas 4 — Regler framåt

1. **Nya primitiver** → `frontend/vue/src/components/ui/` med scoped CSS.
2. **Tokens** → `assets/mrt-color-tokens.css` (aldrig nya hex i komponenter).
3. **App-specifikt** → `frontend/vue/src/styles/<app>/`.
4. **Admin-skals** → `admin/styles/admin-shell.css`.
5. **Import** → `@/components/ui` (publik + admin).

```vue
<MrtButton context="public" variant="primary">Sök resa</MrtButton>
<MrtButton context="admin" variant="primary">Spara</MrtButton>
```

---

## Lager

```
assets/mrt-color-tokens.css     ← designsystem
frontend/vue/src/components/
├── ui/                         ← delade primitiver
├── overview/                   ← domän (delat)
├── timetable-index/            ← domän (publikt)
└── admin/components/ui/        ← admin-specifikt
```

---

## Checklista per ny primitiv

- [ ] `context`-prop om admin + publik
- [ ] Scoped CSS i SFC (inte ny fil i `assets/frontend/ui/`)
- [ ] Tokens — inga nya hex
- [ ] Vitest om klass-logik är icke-trivial
- [ ] Uppdatera [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md)
