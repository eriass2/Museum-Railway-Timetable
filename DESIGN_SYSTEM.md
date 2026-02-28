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
| Token | Värde |
|-------|-------|
| `--mrt-cell-padding` | 4px 6px |
| `--mrt-cell-padding-md` | 6px 8px |
| `--mrt-cell-padding-lg` | 6px 10px |
| `--mrt-spacing-xs` | 0.25rem |
| `--mrt-spacing-sm` | 0.5rem |
| `--mrt-spacing-md` | 1rem |
| `--mrt-spacing-lg` | 1.5rem |
| `--mrt-spacing-xl` | 2rem |

### Border Radius
| Token | Värde |
|-------|-------|
| `--mrt-radius-sm` | 3px |
| `--mrt-radius-md` | 4px |
| `--mrt-radius-lg` | 8px |
| `--mrt-radius-xl` | 12px |

### Font sizes
| Token | Värde |
|-------|-------|
| `--mrt-font-small` | 0.85em |
| `--mrt-font-xs` | 0.8em |

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
| `.mrt-text-tertiary` | Grå text |
| `.mrt-text-error` | Röd feltext |
| `.mrt-text-small` | Mindre fontstorlek |

---

## Riktlinjer

1. **Använd tokens** – Undvik hardkodade färger (#hex). Använd `var(--mrt-*)`.
2. **Kombinera base + modifier** – T.ex. `mrt-card mrt-stat-card` istället för att duplicera alla stilar.
3. **Nya komponenter** – Kontrollera om `.mrt-card`, `.mrt-box` eller `.mrt-section` passar innan du skriver ny CSS.
4. **Radius** – Använd `var(--mrt-radius-sm/md/lg)` istället för 3px, 4px, 8px.
