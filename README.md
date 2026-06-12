# Museum Railway Timetable

A WordPress plugin for displaying train timetables for a museum railway. Custom post types for stations, routes, timetables and services, Vue shortcodes on the frontend, and a Vue admin for data management.

## Features

- **Custom Post Types**: Stations, Routes, Timetables, and Services
- **Custom Taxonomies**: Train Types
- **Shortcodes**: Month calendar, timetable overview, journey wizard, timetable index
- **Admin**: Vue SPA for timetables, routes, stations, import and dev tools
- **Internationalization**: Fully translatable (Swedish included)

## Requirements

- WordPress 6.0 or higher
- PHP 8.2 or higher (recommended; matches CI)

## Installation

1. Upload the plugin files to `/wp-content/plugins/museum-railway-timetable/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Railway Timetable** in the admin menu to configure

**Local development:** `.\local\deploy.ps1 -OpenBrowser` copies the plugin to Local by Flywheel. See [local/README.md](local/README.md).

## Usage

### Shortcodes

| Shortcode | Purpose |
|-----------|---------|
| `[museum_timetable_month]` | Month calendar with traffic days |
| `[museum_timetable_overview]` | Full timetable grid |
| `[museum_journey_wizard]` | Journey search (only public trip planner) |
| `[museum_timetable_index]` | Timetable index |

Full parameters and examples: **[docs/SHORTCODES.md](docs/SHORTCODES.md)**.

Admin workflow (create routes, timetables, trips): **[docs/ADMIN_WORKFLOW.md](docs/ADMIN_WORKFLOW.md)**.

## Development

**Developers:** See **[docs/DEVELOPER.md](docs/DEVELOPER.md)** for Docker, commands, CI and deploy checklist. Documentation index: **[docs/README.md](docs/README.md)**.

Quick start (Windows):

```powershell
.\scripts\mrt.ps1 dev reset      # Docker + import — http://localhost:8080 (admin / admin)
.\scripts\mrt.ps1 check -SkipPhpcs # PHP quality gate (Docker)
```

Unified CLI: **`scripts/mrt.ps1`** / **`scripts/mrt.sh`**. Dev Container: **`.devcontainer/`**.

Docker/skript roadmap: **[docs/DOCKER_SCRIPTS_PLAN.md](docs/DOCKER_SCRIPTS_PLAN.md)** (Fas 0–3 complete). CI model: **[docs/CI_AND_DEV_MODEL.md](docs/CI_AND_DEV_MODEL.md)**.

Contributing: [docs/DEVELOPER.md](docs/DEVELOPER.md#bidra). Coding standards: [docs/REBUILD_RULES.md](docs/REBUILD_RULES.md), [docs/STYLE_GUIDE.md](docs/STYLE_GUIDE.md).

### Hooks and Filters

- `mrt_overview_days_ahead` — days to look ahead in stations overview (default: 60)
- `mrt_should_enqueue_frontend_assets` — force Vue asset enqueue when shortcode is not in post content

### Database

One custom table: `{prefix}_mrt_stoptimes` — stop times for services (arrival/departure can be NULL).

## License

This plugin is provided as-is for use with WordPress.

## Changelog

### Unreleased
- **Journey planner removed**: use `[museum_journey_wizard]` only for public journey search
- **Component demo**: three blocks (month, overview, wizard with `embedded="1"`)
- **Dev menu**: component demo + wizard smoke test only

### 0.4.0
- Journey search shortcode `[museum_journey_planner]` (superseded by wizard)
- Admin documentation for public shortcodes

### 0.3.0
- Timetable overview meta box; direct trip management from timetable edit screen

### 0.2.0
- Inline stop-time editing; REST admin CRUD; streamlined menu

### 0.1.0
- Initial release
