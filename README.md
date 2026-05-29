# Museum Railway Timetable

A WordPress plugin for displaying train timetables for a museum railway. This plugin provides a calendar system with custom post types for stations, routes, and services, along with shortcodes for displaying timetables on the frontend.

## Features

- **Custom Post Types**: Stations, Routes, Timetables, and Services
- **Custom Taxonomies**: Train Types
- **Shortcodes**: Display timetables on the frontend
  - Month calendar view
  - Complete timetable overview
  - **Journey Wizard**: Multi-step journey search with return trips and prices
- **Admin Interface**: 
  - Inline editing for Stop Times directly in Service edit pages
  - Streamlined menu structure
  - Meta boxes for managing service data
  - Stations overview with filtering
  - **Timetable Overview**: Visual preview of timetable grouped by route and direction
  - **Direct Trip Management**: Add, edit, and remove trips directly from Timetable edit screen
- **Internationalization**: Fully translatable (Swedish included)

## Requirements

- WordPress 6.0 or higher
- PHP 8.2 or higher (recommended; matches CI)

## Installation

1. Upload the plugin files to `/wp-content/plugins/museum-railway-timetable/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Railway Timetable** in the admin menu to configure

**Local development:** Use `.\local\deploy.ps1 -OpenBrowser` to copy the plugin to Local by Flywheel and open the site. See [Deploy to Local](#deploy-to-local-wordpress) for setup.

## Usage

### Shortcodes

#### 1. Month Calendar View
Display a calendar showing service days for a month:

```
[museum_timetable_month month="2025-06" train_type="" service="" legend="1" show_counts="1"]
```

**Parameters:**
- `month` - Month in YYYY-MM format (default: current month)
- `train_type` - Filter by train type slug (optional)
- `service` - Filter by specific service name (optional)
- `legend` - Show legend (0 or 1, default: 1)
- `show_counts` - Show service count per day (0 or 1, default: 1)
- `start_monday` - Start week on Monday (0 or 1, default: 1)
- `nav` - Show previous/next month links (0 or 1, default: 1)

#### 2. Timetable Overview
Display a complete timetable overview grouped by route and direction (like traditional printed timetables):

```
[museum_timetable_overview timetable_id="123"]
```

**Parameters:**
- `timetable_id` - Timetable post ID (recommended)
- `timetable` - Timetable name (alternative to timetable_id)

**Features:**
- Groups trips by route and direction (e.g., "Från Uppsala Ö Till Marielund")
- Shows train types with PNG icons (steam, diesel, railbus, bus) for each trip
- Displays times for each station, with "X" for null/unspecified times
- Perfect for displaying complete timetables on pages

#### 3. Journey Wizard (recommended)
Multi-step booking flow with calendar, outbound/return selection, train-type icons, and price summary:

```
[museum_journey_wizard ticket_url="https://example.com/tickets"]
```

**Parameters:** `ticket_url`, `hero_subtitle`, `timetable_id`, `timetable` (optional printed overview under step 1). See [docs/SHORTCODES.md](docs/SHORTCODES.md).

### Managing Services

Services can be managed in two ways:

#### Route-Based Editing (Recommended)
1. **Create a Route first:**
   - Go to **Railway Timetable > Routes** and create a new route
   - Add stations to the route in order using the "Route Stations" meta box
   - **Use ↑ ↓ buttons to easily reorder stations** - much easier than removing and re-adding
2. **Create a Timetable:**
   - Go to **Railway Timetable > Timetables** and create a new timetable
   - Add dates (YYYY-MM-DD) when this timetable applies
   - A timetable can have multiple dates (e.g., all weekends in a month)
   - **View the "Timetable Overview" meta box** to see a visual preview of all trips grouped by route and direction
3. **Add Trips to Timetable (Recommended):**
   - In the **Trips (Services)** meta box on the Timetable edit screen, you can directly add trips
   - Select a **Route** (required)
   - Select a **Train Type** (optional)
   - Select a **Destination** (end station on the route)
   - Click **"Add Trip"** - the trip will be automatically created and linked to this timetable
   - Trip titles are generated from the route and destination
4. **Edit Trips:**
   - Click **"Edit"** on any trip in the timetable to configure Stop Times
   - Or go to **Railway Timetable > Services** to edit trips directly
5. **Configure Stop Times:**
   - In the **Stop Times** meta box, all stations on the selected route are displayed
   - Check "Stops here" for each station where the train stops
   - Fill in Arrival/Departure times (can be empty if time is not fixed)
   - Select Pickup/Dropoff options
   - Click "Save Stop Times" to save all changes at once


## Planned Features

The following features are planned for future releases:

1. **Approximate Departure Times**
   - Ability to mark departures as approximate (e.g., "ca. 10:00" or "~10:00")
   - Useful for services with flexible timing

2. **Symbol Legend** (extended)
   - Add a legend explaining all symbols used in timetables
   - Will help users understand the various indicators and markings

These features will enhance the flexibility and clarity of timetable displays, making it easier to represent complex railway operations.

## Development

**Utvecklare:** Se [docs/DEVELOPER.md](docs/DEVELOPER.md) (`composer plugin-check`, CI). Bidrag: [CONTRIBUTING.md](CONTRIBUTING.md).

### Docker WordPress Development

You can run the plugin in a local WordPress install without installing PHP locally. The Docker setup starts WordPress, MariaDB, installs WordPress, and activates this plugin automatically.

Requirements:
- Docker Desktop or another Docker Compose compatible runtime

Start the site:

```sh
docker compose up -d --build
```

Open:
- Site: http://localhost:8080
- Admin: http://localhost:8080/wp-admin

Default admin login:
- Username: `admin`
- Password: `admin`

Docker dev uses **Swedish** (`sv_SE`) for WordPress core and the admin user. Fresh installs get this automatically; on an existing volume run `docker compose run --rm wordpress-init sh /usr/local/bin/mrt-ensure-sv-locale.sh`.

The repository is mounted into WordPress at:

```text
/var/www/html/wp-content/plugins/museum-railway-timetable
```

So edits in this checkout are reflected immediately in the running plugin.

Useful commands:

```sh
docker compose logs -f wordpress
docker compose down
docker compose down -v
```

`docker compose down -v` removes the WordPress and database volumes, giving you a fresh install next time.

Run Composer inside Docker:

```sh
docker compose run --rm composer install
docker compose run --rm composer test
docker compose run --rm composer lint
```

### Deploy to Local (WordPress)

To automate copying the plugin to your Local by Flywheel site for testing:

1. **First time:** Copy `local/deploy.config.example.json` to `local/deploy.config.json` and edit with your Local site path and URL.
2. Run:
   ```powershell
   .\local\deploy.ps1              # Copy plugin files only
   .\local\deploy.ps1 -OpenBrowser  # Copy and open localhost in browser
   ```

The script copies `inc/`, `assets/`, `languages/`, and main plugin files to your Local site's plugins folder.

### Project Structure

```
museum-railway-timetable/
├─ museum-railway-timetable.php  # Main plugin file
├─ uninstall.php                 # Uninstall hook
├─ composer.json                 # Dev tools (PHPStan, PHPCS)
├─ phpcs.xml, phpstan.neon       # Lint config
├─ docs/                         # Developer docs (see docs/README.md; subfolders by topic)
├─ local/                        # deploy.ps1, deploy.config (Local by Flywheel)
├─ scripts/                      # validate.php, lint.ps1
├─ inc/
│   ├─ bootstrap.php             # Loads domain + app
│   ├─ bootstrap/domain.php
│   ├─ constants.php
│   ├─ domain/                   # journey, service, timetable, pricing, …
│   ├─ admin/                    # dashboard/, meta-boxes/, tools/
│   ├─ public/                   # month-calendar, timetable-overview, journey-wizard
│   ├─ infrastructure/           # post-types/, ajax/, wordpress/
│   ├─ import/lennakatten/
│   ├─ assets/                   # enqueue helpers
│   ├─ assets.php, admin.php, shortcodes.php
├─ assets/
│   ├─ admin.js                  # Admin entry (depends on modules below)
│   ├─ admin-utils.js
│   ├─ admin-route-ui.js
│   ├─ admin-stoptimes-ui.js
│   ├─ admin-timetable-services-ui.js
│   ├─ admin-service-edit.js     # Service edit (route, stoptimes form)
│   ├─ dist/vue/                 # Vite bundle (public shortcode JS + CSS)
│   ├─ icons/train-types/        # PNG train type symbols
│   ├─ train-type-icons.css      # Minimal icon mapping retained during rebuild
│   └─ frontend.js               # Month calendar AJAX (legacy; Vue mode uses dist/vue)
└─ languages/                    # .pot / .po
```

### Coding Standards

Se [docs/REBUILD_RULES.md](docs/REBUILD_RULES.md) och [docs/STYLE_GUIDE.md](docs/STYLE_GUIDE.md). Översikt: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

### Hooks and Filters

**Filters:**
- `mrt_overview_days_ahead` - Number of days to look ahead in stations overview (default: 60)
- `mrt_should_enqueue_frontend_assets` - Control frontend asset loading

### Database Tables

The plugin creates one custom table:
- `{prefix}_mrt_stoptimes` - Stop times for services (arrival/departure times can be NULL)

## Contributing

1. Läs [docs/DEVELOPER.md](docs/DEVELOPER.md) och [docs/README.md](docs/README.md)
2. Följ [docs/REBUILD_RULES.md](docs/REBUILD_RULES.md) och [docs/STYLE_GUIDE.md](docs/STYLE_GUIDE.md)
3. Add PHPDoc comments to all functions
4. Ensure all output is properly escaped
5. Test your changes thoroughly

## License

This plugin is provided as-is for use with WordPress.

## Changelog

### Unreleased
- **Journey planner removed**: use `[museum_journey_wizard]` only for public journey search
- **Component demo**: three blocks (month, overview, wizard with `embedded="1"`)
- **Dev menu**: component demo + wizard smoke test only

### 0.4.0
- **Journey Planner**: New shortcode `[museum_journey_planner]` for searching connections between stations (removed in favor of wizard)
- **Admin Documentation**: Comprehensive documentation for all shortcodes including journey planner
- **Enhanced Help**: Improved admin page with detailed shortcode usage instructions

### 0.3.0
- **Timetable Overview**: Visual preview of timetable grouped by route and direction, showing train types and times
- **Direct Trip Management**: Add, edit, and remove trips directly from Timetable edit screen
- **Automatic Trip Naming**: Trips are automatically named based on Route + Direction (no manual naming required)
- **Improved Workflow**: Streamlined process for managing trips within timetables

### 0.2.0
- **Inline Editing**: Click-to-edit functionality for Stop Times
- **Streamlined Menu**: Cleaned up admin menu structure
- **Enhanced UX**: Direct editing in Service edit pages without separate forms
- **AJAX Operations**: All CRUD operations use AJAX for better performance
- **Improved UI**: Visual feedback for editing mode with hover effects

### 0.1.0
- Initial release
- Custom post types for stations, routes, and services
- Shortcodes for timetable display
- Admin interface for management
