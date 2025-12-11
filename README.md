# Museum Railway Timetable

A WordPress plugin for displaying train timetables for a museum railway. This plugin provides a calendar system with custom post types for stations, routes, and services, along with shortcodes for displaying timetables on the frontend.

## Features

- **Custom Post Types**: Stations, Routes, Timetables, and Services
- **Custom Taxonomies**: Train Types
- **Shortcodes**: Display timetables on the frontend
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
- PHP 8.0 or higher

## Installation

1. Upload the plugin files to `/wp-content/plugins/museum-railway-timetable/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Railway Timetable** in the admin menu to configure

## Usage

### Shortcodes

#### 1. Simple Timetable
Display next departures from a station:

```
[museum_timetable station="Station Name" limit="5" show_arrival="1" train_type="steam"]
```

**Parameters:**
- `station` - Station name (or use `station_id`)
- `station_id` - Station post ID
- `limit` - Number of departures to show (default: 5)
- `show_arrival` - Show arrival times (0 or 1, default: 0)
- `train_type` - Filter by train type slug (optional)

#### 2. Station Picker
Display a dropdown to select a station and show its timetable:

```
[museum_timetable_picker default_station="Station Name" limit="6" show_arrival="1"]
```

**Parameters:**
- `default_station` - Default selected station name
- `limit` - Number of departures to show (default: 6)
- `show_arrival` - Show arrival times (0 or 1, default: 0)
- `train_type` - Filter by train type slug (optional)
- `form_method` - Form submission method: "get" or "post" (default: "get")
- `placeholder` - Placeholder text for dropdown

#### 3. Month Calendar View
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

#### 4. Timetable Overview
Display a complete timetable overview grouped by route and direction (like traditional printed timetables):

```
[museum_timetable_overview timetable_id="123"]
```

**Parameters:**
- `timetable_id` - Timetable post ID (recommended)
- `timetable` - Timetable name (alternative to timetable_id)

**Features:**
- Groups trips by route and direction (e.g., "Från Uppsala Ö Till Marielund")
- Shows train types (Ångtåg, Rälsbuss, Dieseltåg) for each trip
- Displays times for each station, with "X" for null/unspecified times
- Perfect for displaying complete timetables on pages

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
   - Select a **Direction** (optional: "Dit" or "Från")
   - Click **"Add Trip"** - the trip will be automatically created and linked to this timetable
   - Trips are automatically named based on Route + Direction
4. **Edit Trips:**
   - Click **"Edit"** on any trip in the timetable to configure Stop Times
   - Or go to **Railway Timetable > Services** to edit trips directly
5. **Configure Stop Times:**
   - In the **Stop Times** meta box, all stations on the selected route are displayed
   - Check "Stops here" for each station where the train stops
   - Fill in Arrival/Departure times (can be empty if time is not fixed)
   - Select Pickup/Dropoff options
   - Click "Save Stop Times" to save all changes at once


## Development

### Project Structure

```
museum-railway-timetable/
├─ museum-railway-timetable.php  # Main plugin file
├─ uninstall.php                 # Uninstall hook
├─ inc/
│   ├─ functions/
│   │   ├─ helpers.php           # Helper functions
│   │   ├─ services.php          # Service-related functions
│   │   └─ timetable-view.php    # Timetable overview rendering
│   ├─ assets.php                # Asset enqueuing
│   ├─ admin-page.php            # Main admin page and menu
│   ├─ admin-list.php            # Stations overview
│   ├─ admin-meta-boxes.php      # Meta boxes for CPTs (inline editing)
│   ├─ admin-ajax.php            # AJAX handlers for CRUD operations
│   ├─ cpt.php                   # Custom post types
│   └─ shortcodes.php            # Shortcode registrations
├─ assets/
│   ├─ admin.css                 # Admin and frontend styles
│   └─ admin.js                  # Admin JavaScript (inline editing)
└─ languages/                    # Translation files
```

### Coding Standards

This plugin follows WordPress coding standards and best practices.

### Hooks and Filters

**Filters:**
- `mrt_overview_days_ahead` - Number of days to look ahead in stations overview (default: 60)
- `mrt_should_enqueue_frontend_assets` - Control frontend asset loading

### Database Tables

The plugin creates one custom table:
- `{prefix}_mrt_stoptimes` - Stop times for services (arrival/departure times can be NULL)

## Contributing

1. Follow the WordPress coding standards
2. Add PHPDoc comments to all functions
3. Ensure all output is properly escaped
4. Test your changes thoroughly

## License

This plugin is provided as-is for use with WordPress.

## Changelog

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
- Three shortcodes for timetable display
- Admin interface for management

