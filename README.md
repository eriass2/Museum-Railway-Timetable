# Museum Railway Timetable

A WordPress plugin for displaying train timetables for a museum railway. This plugin provides a calendar system with custom post types for stations, routes, and services, along with shortcodes for displaying timetables on the frontend.

## Features

- **Custom Post Types**: Stations, Routes, and Services
- **Custom Taxonomies**: Train Types
- **CSV Import**: Import stations, stop times, and calendar data via CSV
- **Shortcodes**: Display timetables on the frontend
- **Admin Interface**: Manage stations, services, and import data
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

### CSV Import

The plugin includes a CSV import feature for bulk importing data:

1. Go to **Railway Timetable > CSV Import** in the admin menu
2. Select the import type (Stations, Stop Times, or Calendar)
3. Paste your CSV data or download sample files
4. Click Import

**CSV Formats:**

#### Stations
```
name,station_type,lat,lng,display_order
Hultsfred Museum,station,57.486,15.842,1
```

#### Stop Times
```
service,station,sequence,arrive,depart,pickup,dropoff
Steam Train A,Hultsfred Museum,1,,10:00,1,1
```

#### Calendar
```
service,start_date,end_date,mon,tue,wed,thu,fri,sat,sun,include_dates,exclude_dates
Steam Train A,2025-06-01,2025-08-31,0,0,0,0,0,1,1,2025-06-06,
```

See the import page for detailed format specifications and examples.

## Development

### Project Structure

```
museum-railway-timetable/
├─ museum-railway-timetable.php  # Main plugin file
├─ uninstall.php                 # Uninstall hook
├─ inc/
│   ├─ functions/
│   │   ├─ helpers.php           # Helper functions
│   │   └─ services.php          # Service-related functions
│   ├─ import/
│   │   ├─ csv-parser.php        # CSV parsing
│   │   ├─ import-handlers.php   # Import logic
│   │   ├─ import-page.php       # Admin import page
│   │   ├─ sample-csv.php        # Sample CSV generators
│   │   └─ download-handler.php  # CSV download handler
│   ├─ assets.php                # Asset enqueuing
│   ├─ admin-page.php            # Main admin page
│   ├─ admin-list.php            # Stations overview
│   ├─ cpt.php                   # Custom post types
│   ├─ shortcodes.php            # Shortcode registrations
│   └─ import.php                # Import loader
├─ assets/
│   ├─ admin.css
│   └─ admin.js
└─ languages/                    # Translation files
```

### Coding Standards

This plugin follows the WordPress Plugin Style Guide. See `wordpress-plugin-style-guide.md` for details.

### Hooks and Filters

**Filters:**
- `mrt_overview_days_ahead` - Number of days to look ahead in stations overview (default: 60)
- `mrt_should_enqueue_frontend_assets` - Control frontend asset loading

### Database Tables

The plugin creates two custom tables:
- `{prefix}_mrt_stoptimes` - Stop times for services
- `{prefix}_mrt_calendar` - Calendar data for service schedules

## Contributing

1. Follow the WordPress coding standards
2. Add PHPDoc comments to all functions
3. Ensure all output is properly escaped
4. Test your changes thoroughly

## License

This plugin is provided as-is for use with WordPress.

## Changelog

### 0.1.0
- Initial release
- Custom post types for stations, routes, and services
- CSV import functionality
- Three shortcodes for timetable display
- Admin interface for management

