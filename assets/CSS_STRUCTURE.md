# CSS-filstruktur

CSS-filerna är uppdelade i mindre, specifika filer. Huvudfilerna importerar sina delar via `@import`.

## Översikt

| Huvudfil | Importerar |
|----------|------------|
| **admin-base.css** | tokens, components, utilities |
| **admin-components.css** | form, ui, width |
| **admin-timetable.css** | table, month |
| **admin-timetable-overview.css** | layout, cells, components |
| **admin-meta-boxes.css** | fields, edit |
| **admin-dashboard.css** | guide, misc |
| **admin-ui.css** | status, journey |
| **admin-responsive.css** | (ingen split) |

## Detaljer

### admin-base
- `admin-base-tokens.css` – Design tokens (färger, spacing, typografi)
- `admin-base-components.css` – Card, box, alert, section, grid
- `admin-base-utilities.css` – Margin, display, text utilities

### admin-components
- `admin-components-form.css` – Form, label, input, form-row, form-fields
- `admin-components-ui.css` – Button, badge, heading, empty, card variants
- `admin-components-width.css` – Width utilities (mrt-w-*), responsive form

### admin-timetable
- `admin-timetable-table.css` – Tabellkomponent
- `admin-timetable-month.css` – Månadskalender, dagceller

### admin-dashboard
- `admin-dashboard-guide.css` – Guide, info-box
- `admin-dashboard-misc.css` – Formulär, kod, validering

### admin-meta-boxes
- `admin-meta-boxes-fields.css` – Meta-fält, stoptimes-struktur
- `admin-meta-boxes-edit.css` – Inline-redigering, nya rader

### admin-ui
- `admin-ui-status.css` – Statusmeddelanden, laddning, animationer
- `admin-ui-journey.css` – Reseplanerare

## Enqueue-ordning (inc/assets.php)

1. admin-base
2. admin-components
3. admin-timetable
4. admin-timetable-overview
5. admin-meta-boxes
6. admin-dashboard
7. admin-ui
8. admin-responsive
