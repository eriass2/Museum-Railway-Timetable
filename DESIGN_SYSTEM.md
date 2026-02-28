# Design System – Museum Railway Timetable

Gemensamt utseende och återanvändbara komponenter för att minska unik CSS.

---

## Design Tokens (admin-base.css)

### Färger
| Token | Användning |
|-------|------------|
| `--mrt-green-primary` | Rutter, rubriker |
| `--mrt-success` | Sparad-indikator, lyckade tillstånd |
| `--mrt-text-error` | Felmeddelanden, validering |
| `--mrt-bg-light` | Ljusgrå bakgrund |
| `--mrt-box-bg` | Box-sektioner |
| `--mrt-card-bg` | Kort (statistik) |

### Spacing
5 nivåer – använd närmaste vid behov.

| Token | Värde | Användning |
|-------|-------|------------|
| `--mrt-spacing-xs` | 0.25rem (4px) | Små mellanrum, tight gap |
| `--mrt-spacing-sm` | 0.5rem (8px) | Kompakta sektioner |
| `--mrt-spacing-md` | 1rem (16px) | Standard |
| `--mrt-spacing-lg` | 1.5rem (24px) | Sektioner, block |
| `--mrt-spacing-xl` | 2rem (32px) | Stora avstånd |

**Cell-padding** (tabeller):
| Token | Värde |
|-------|-------|
| `--mrt-cell-padding` | 4px 6px |
| `--mrt-cell-padding-md` | 6px 8px |
| `--mrt-cell-padding-lg` | 6px 10px |

### Border Radius
4 nivåer – använd närmaste.

| Token | Värde | Användning |
|-------|-------|------------|
| `--mrt-radius-xs` | 2px | Chips, tight |
| `--mrt-radius-sm` | 4px | Buttons, inputs |
| `--mrt-radius-md` | 8px | Cards |
| `--mrt-radius-lg` | 12px | Modals |

### Font sizes
6 nivåer – använd närmaste.

| Token | Värde | Användning |
|-------|-------|------------|
| `--mrt-font-xs` | 0.75rem | Små lablar, chip |
| `--mrt-font-sm` | 0.85rem | Mindre text |
| `--mrt-font-base` | 0.9rem | Standard |
| `--mrt-font-md` | 1rem | |
| `--mrt-font-lg` | 1.1rem | Rubriker |
| `--mrt-font-xl` | 1.2rem | Större rubriker |

### Shadows
4 nivåer + fokus-varianter.

| Token | Värde |
|-------|-------|
| `--mrt-shadow-sm` | 0 1px 3px rgba(0,0,0,0.1) |
| `--mrt-shadow-md` | 0 2px 4px rgba(0,0,0,0.1) |
| `--mrt-shadow-lg` | 0 2px 8px rgba(0,0,0,0.08) |
| `--mrt-shadow-xl` | 0 4px 12px rgba(0,0,0,0.08) |
| `--mrt-shadow-side` | Sidokant |
| `--mrt-shadow-focus` | Gul fokusring |
| `--mrt-shadow-focus-ring` | Blå fokusring |
| `--mrt-shadow-focus-error` | Röd fokusring |
| `--mrt-shadow-focus-wp` | WP-blå fokusring |

### Opacity
4 nivåer.

| Token | Värde |
|-------|-------|
| `--mrt-opacity-50` | 0.5 |
| `--mrt-opacity-70` | 0.7 |
| `--mrt-opacity-85` | 0.85 |
| `--mrt-opacity-95` | 0.95 |

### Sizes
| Token | Värde |
|-------|-------|
| `--mrt-size-dot` | 0.6rem (legend-prickar) |

### Timetable-specifika
| Token | Användning |
|-------|------------|
| `--mrt-from-to-bg` | Från/Till-rad bakgrund |
| `--mrt-from-to-col-bg` | Från/Till station-kolumn |
| `--mrt-transfer-bg` | Tågbyte-rad |
| `--mrt-transfer-col-bg` | Tågbyte station-kolumn |
| `--mrt-route-header-end` | Gradient slut (route header) |

---

## Base Components

### .mrt-card
Vit bakgrund, kant, rundade hörn. Används för statistik-kort.

```html
<div class="mrt-card mrt-stat-card">...</div>
```

### .mrt-box
Ljusgrå sektion för formulärdelar (datumväljare, slutstationer).

```html
<div class="mrt-box mrt-date-pattern-section">...</div>
```

### .mrt-box-sm
Mindre variant för rader i listor.

```html
<div class="mrt-box mrt-box-sm mrt-train-type-date-row">...</div>
```

### .mrt-section
Större container för dashboard-sektioner.

```html
<div class="mrt-section mrt-settings-section">...</div>
```

### .mrt-alert
Vänsterkant-box för info/varning/fel. Används av mrt-info-box och mrt-tip-box.

```html
<div class="mrt-alert mrt-alert-info mrt-info-box">...</div>
<p class="mrt-alert mrt-alert-warning mrt-tip-box">...</p>
<div class="mrt-alert mrt-alert-error">...</div>
<div class="mrt-alert mrt-alert-info mrt-none">...</div>  <!-- tomt tillstånd -->
```

### .mrt-row-hover
Hover-effekt för interaktiva tabellrader.

```html
<tr class="mrt-row-hover">...</tr>
```

### .mrt-grid
Responsiv grid med gap.

```html
<div class="mrt-grid mrt-grid-auto">...</div>
```

---

## Utilities

| Klass | Effekt |
|-------|--------|
| `.mrt-mt-0` | margin-top: 0 |
| `.mrt-mt-1` | margin-top: var(--mrt-spacing-md) |
| `.mrt-mb-1` | margin-bottom: var(--mrt-spacing-md) |
| `.mrt-ml-1` | margin-left: var(--mrt-spacing-md) |
| `.mrt-my-1` | margin-top/bottom: var(--mrt-spacing-md) |
| `.mrt-loading-cell` | Centrerad padding för laddningscell |
| `.mrt-spinner-inline` | Inline-spinner utan float |
| `.mrt-text-tertiary` | Grå text |
| `.mrt-text-error` | Röd feltext |
| `.mrt-text-small` | Mindre fontstorlek |

---

## Riktlinjer

1. **Använd tokens** – Undvik hardkodade färger (#hex). Använd `var(--mrt-*)`.
2. **Kombinera base + modifier** – T.ex. `mrt-card mrt-stat-card` istället för att duplicera alla stilar.
3. **Nya komponenter** – Kontrollera om `.mrt-card`, `.mrt-box` eller `.mrt-section` passar innan du skriver ny CSS.
4. **Tokens** – Använd `var(--mrt-*)` med närmaste nivå (xs/sm/md/lg/xl).
