# Data Model Documentation

This document describes all data objects in the Museum Railway Timetable plugin and their relationships.

## Overview

The plugin uses a combination of WordPress Custom Post Types, Taxonomies, Custom Database Tables, and Post Meta to manage timetable data.

```
┌─────────────────┐
│   Station       │
│  (CPT)          │
└────────┬────────┘
         │
         │ (many-to-many via mrt_stoptimes)
         │
         ▼
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│   Stop Time     │◄─────┤   Service       │◄─────┤   Route         │
│  (Custom Table) │      │   (CPT)         │      │   (CPT)         │
└─────────────────┘      └────────┬────────┘      └─────────────────┘
                                  │
                                  │ (one-to-many)
                                  │
                                  ▼
                         ┌─────────────────┐
                         │   Calendar      │
                         │  (Custom Table) │
                         └─────────────────┘
                                  │
                                  │ (many-to-one)
                                  │
                                  ▼
                         ┌─────────────────┐
                         │  Train Type     │
                         │  (Taxonomy)     │
                         └─────────────────┘
```

---

## 1. Custom Post Types

### 1.1 Station (`mrt_station`)

**Description:** Represents a physical station, halt, depot, or museum location.

**Storage:** WordPress `wp_posts` table (post_type = 'mrt_station')

**Fields:**
- **Title** (post_title) - Station name (required)
- **Post ID** (ID) - Unique identifier

**Meta Fields:**
- `mrt_station_type` (string) - Type of station
  - Values: `'station'`, `'halt'`, `'depot'`, `'museum'`, or empty
- `mrt_lat` (float) - Latitude coordinate (optional)
- `mrt_lng` (float) - Longitude coordinate (optional)
- `mrt_display_order` (int) - Display order for sorting (default: 0)

**Relationships:**
- **Many-to-Many** with `Service` via `mrt_stoptimes` table
- Each station can have multiple stop times (different services stopping there)
- Each service can stop at multiple stations

**Usage:**
- Used in shortcodes to display timetables
- Referenced in `mrt_stoptimes.station_post_id`

---

### 1.2 Service (`mrt_service`)

**Description:** Represents a scheduled train trip/service (e.g., "Morning Express", "Steam Train Tour").

**Storage:** WordPress `wp_posts` table (post_type = 'mrt_service')

**Fields:**
- **Title** (post_title) - Service name (required)
- **Post ID** (ID) - Unique identifier

**Meta Fields:**
- `mrt_service_route_id` (int) - Route post ID that this service runs on (required)
  - Links service to a route
  - Used to filter available stations when configuring stop times
- `mrt_direction` (string) - Direction of the service (optional)
  - Example: "Northbound", "Southbound"

**Taxonomies:**
- `mrt_train_type` - Train type taxonomy (many-to-many)
  - Links service to train types (e.g., "steam", "diesel", "electric")

**Relationships:**
- **Many-to-One** with `Route` (via `mrt_service_route_id` meta field)
  - Each service is linked to one route
  - Multiple services can use the same route
- **One-to-Many** with `StopTime` (via `mrt_stoptimes.service_post_id`)
- **One-to-Many** with `Calendar` (via `mrt_calendar.service_post_id`)
- **Many-to-Many** with `Station` (via `mrt_stoptimes` table)
- **Many-to-Many** with `TrainType` (via taxonomy)

**Usage:**
- Defines which stations a train stops at and when
- Linked to calendar entries to determine when service runs
- Can be filtered by train type in shortcodes

---

### 1.3 Route (`mrt_route`)

**Description:** Defines a route (line) with a sequence of stations. Routes are used to organize services and simplify stop time configuration.

**Storage:** WordPress `wp_posts` table (post_type = 'mrt_route')

**Fields:**
- **Title** (post_title) - Route name (required)
  - Example: "Hultsfred → Västervik", "Main Line", "Northbound"
- **Post ID** (ID) - Unique identifier

**Meta Fields:**
- `mrt_route_stations` (array) - Array of station post IDs in order
  - Defines which stations are on the route and their sequence
  - Example: `[123, 456, 789]` means station 123 is first, 456 is second, etc.

**Relationships:**
- **One-to-Many** with `Service` (via `mrt_service_route_id` meta field)
  - Multiple services can use the same route
  - Each service can be linked to one route

**Usage:**
- Used in Service edit screen to automatically display all stations on the route
- Simplifies stop time configuration - user selects which stations the train stops at
- Routes can be reused for multiple services (e.g., 12 departures per direction)
- Routes can work in both directions (create separate routes for each direction if needed)

---

## 2. Taxonomies

### 2.1 Train Type (`mrt_train_type`)

**Description:** Categorizes services by train type (e.g., steam, diesel, electric).

**Storage:** WordPress taxonomy tables (`wp_terms`, `wp_term_taxonomy`, `wp_term_relationships`)

**Fields:**
- **Name** (term name) - Train type name (required)
- **Slug** (term slug) - URL-friendly identifier (auto-generated)
- **Term ID** - Unique identifier

**Relationships:**
- **Many-to-Many** with `Service` (via WordPress taxonomy system)
  - One service can have multiple train types
  - One train type can be assigned to multiple services

**Usage:**
- Used to filter services in shortcodes (`train_type` parameter)
- Used in admin interface for filtering stations overview
- Displayed in timetable views

**Example Values:**
- "steam"
- "diesel"
- "electric"
- "heritage"

---

## 3. Custom Database Tables

### 3.1 Stop Times (`{prefix}_mrt_stoptimes`)

**Description:** Stores the schedule of when services stop at stations, including arrival/departure times and sequence.

**Table Structure:**
```sql
CREATE TABLE {prefix}_mrt_stoptimes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    service_post_id BIGINT UNSIGNED NOT NULL,
    station_post_id BIGINT UNSIGNED NOT NULL,
    stop_sequence INT NOT NULL,
    arrival_time CHAR(5) NULL,
    departure_time CHAR(5) NULL,
    pickup_allowed TINYINT(1) DEFAULT 1,
    dropoff_allowed TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    KEY service_seq (service_post_id, stop_sequence),
    KEY station (station_post_id)
)
```

**Fields:**
- `id` (BIGINT) - Primary key, auto-increment
- `service_post_id` (BIGINT) - Foreign key to `wp_posts.ID` (mrt_service)
- `station_post_id` (BIGINT) - Foreign key to `wp_posts.ID` (mrt_station)
- `stop_sequence` (INT) - Order of stop in service route (1, 2, 3, ...)
- `arrival_time` (CHAR(5)) - Arrival time in HH:MM format (nullable)
- `departure_time` (CHAR(5)) - Departure time in HH:MM format (nullable)
- `pickup_allowed` (TINYINT) - Whether passengers can board (default: 1)
- `dropoff_allowed` (TINYINT) - Whether passengers can alight (default: 1)

**Relationships:**
- **Many-to-One** with `Service` (via `service_post_id`)
- **Many-to-One** with `Station` (via `station_post_id`)

**Indexes:**
- Primary key on `id`
- Composite index on `(service_post_id, stop_sequence)` for efficient service route queries
- Index on `station_post_id` for efficient station queries

**Usage:**
- Core table for timetable functionality
- Used by shortcodes to display departure/arrival times
- Created via admin interface

**Example Data:**
```
service_post_id | station_post_id | stop_sequence | arrival_time | departure_time
----------------|-----------------|---------------|--------------|----------------
123            | 45              | 1             | NULL         | 09:00
123            | 46              | 2             | 09:15        | 09:20
123            | 47              | 3             | 09:35        | NULL
```

---

### 3.2 Calendar (`{prefix}_mrt_calendar`)

**Description:** Defines when services run, including date ranges, days of week, and exception dates.

**Table Structure:**
```sql
CREATE TABLE {prefix}_mrt_calendar (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    service_post_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    mon TINYINT(1) DEFAULT 0,
    tue TINYINT(1) DEFAULT 0,
    wed TINYINT(1) DEFAULT 0,
    thu TINYINT(1) DEFAULT 0,
    fri TINYINT(1) DEFAULT 0,
    sat TINYINT(1) DEFAULT 0,
    sun TINYINT(1) DEFAULT 0,
    include_dates TEXT NULL,
    exclude_dates TEXT NULL,
    PRIMARY KEY (id),
    KEY service (service_post_id),
    KEY range_idx (start_date, end_date)
)
```

**Fields:**
- `id` (BIGINT) - Primary key, auto-increment
- `service_post_id` (BIGINT) - Foreign key to `wp_posts.ID` (mrt_service)
- `start_date` (DATE) - First date service runs (YYYY-MM-DD)
- `end_date` (DATE) - Last date service runs (YYYY-MM-DD)
- `mon` (TINYINT) - Runs on Monday (0 or 1)
- `tue` (TINYINT) - Runs on Tuesday (0 or 1)
- `wed` (TINYINT) - Runs on Wednesday (0 or 1)
- `thu` (TINYINT) - Runs on Thursday (0 or 1)
- `fri` (TINYINT) - Runs on Friday (0 or 1)
- `sat` (TINYINT) - Runs on Saturday (0 or 1)
- `sun` (TINYINT) - Runs on Sunday (0 or 1)
- `include_dates` (TEXT) - Comma-separated list of additional dates (YYYY-MM-DD format)
- `exclude_dates` (TEXT) - Comma-separated list of dates to exclude (YYYY-MM-DD format)

**Relationships:**
- **Many-to-One** with `Service` (via `service_post_id`)

**Indexes:**
- Primary key on `id`
- Index on `service_post_id` for efficient service queries
- Composite index on `(start_date, end_date)` for efficient date range queries

**Usage:**
- Determines which dates a service runs
- Used by `MRT_services_running_on_date()` to find active services
- Created via admin interface

**Example Data:**
```
service_post_id | start_date  | end_date    | sat | sun | exclude_dates
----------------|-------------|-------------|-----|-----|------------------
123            | 2025-06-01  | 2025-08-31  | 1   | 1   | 2025-07-04,2025-08-15
```

---

## 4. WordPress Options

### 4.1 Plugin Settings (`mrt_settings`)

**Description:** Stores plugin configuration options.

**Storage:** WordPress `wp_options` table

**Structure:**
```php
[
    'enabled' => bool,  // Whether plugin is enabled
    'note' => string   // Optional note/notice text
]
```

**Default Values:**
```php
[
    'enabled' => true,
    'note' => ''
]
```

**Usage:**
- Controls whether timetable functionality is active
- Stores optional note text for display

---

## 5. Data Relationships Summary

### Primary Relationships

1. **Service ↔ Station** (Many-to-Many)
   - Via: `mrt_stoptimes` table
   - A service stops at multiple stations
   - A station is served by multiple services

2. **Service → StopTime** (One-to-Many)
   - Via: `mrt_stoptimes.service_post_id`
   - One service has multiple stop times (one per station)

3. **Station → StopTime** (One-to-Many)
   - Via: `mrt_stoptimes.station_post_id`
   - One station has multiple stop times (from different services)

4. **Service → Calendar** (One-to-Many)
   - Via: `mrt_calendar.service_post_id`
   - One service can have multiple calendar entries (different date ranges)

5. **Service ↔ TrainType** (Many-to-Many)
   - Via: WordPress taxonomy system
   - A service can have multiple train types
   - A train type can be assigned to multiple services

### Query Patterns

**Find all services stopping at a station:**
```sql
SELECT DISTINCT service_post_id 
FROM mrt_stoptimes 
WHERE station_post_id = {station_id}
```

**Find all stations for a service:**
```sql
SELECT station_post_id, stop_sequence, arrival_time, departure_time
FROM mrt_stoptimes
WHERE service_post_id = {service_id}
ORDER BY stop_sequence
```

**Find services running on a specific date:**
```sql
SELECT service_post_id
FROM mrt_calendar
WHERE start_date <= {date} 
  AND end_date >= {date}
  AND {day_of_week} = 1
  AND (exclude_dates IS NULL OR {date} NOT IN exclude_dates)
  OR (include_dates IS NOT NULL AND {date} IN include_dates)
```

---

## 6. Data Flow

### Data Creation Flow
1. **Stations** → Created via admin interface (`mrt_station` posts with meta fields)
2. **Routes** → Created via admin interface (`mrt_route` posts with station sequence)
3. **Services** → Created via admin interface (`mrt_service` posts linked to routes)
4. **Stop Times** → Created via admin interface (inserts into `mrt_stoptimes` table)
5. **Calendar** → Created via admin interface (inserts into `mrt_calendar` table)

### Display Flow
1. **Shortcode** → Queries services running on date
2. **Service Query** → Checks `mrt_calendar` for active services
3. **Stop Time Query** → Gets stops from `mrt_stoptimes` for service
4. **Station Lookup** → Gets station details from `mrt_station` posts
5. **Train Type Filter** → Filters via taxonomy if specified

---

## 7. Key Functions

### Helper Functions
- `MRT_get_all_stations()` - Get all stations ordered by display order
- `MRT_get_post_by_title()` - Find post by title and type
- `MRT_get_current_datetime()` - Get current date/time info

### Service Functions
- `MRT_services_running_on_date()` - Find services active on a date
- `MRT_get_services_for_station()` - Get services stopping at station
- `MRT_next_running_day_for_station()` - Find next service day for station


---

## 8. Data Integrity

### Constraints
- **Stop Times**: `service_post_id` and `station_post_id` must reference valid posts
- **Calendar**: `service_post_id` must reference valid service post
- **Stop Sequence**: Must be unique per service (enforced in application logic)
- **Date Ranges**: `end_date` should be >= `start_date` (enforced in application logic)

### Validation
- Time formats: HH:MM (24-hour format)
- Date formats: YYYY-MM-DD
- Station types: Whitelist validation (station, halt, depot, museum)
- Display order: Integer >= 0

---

## 9. Future Considerations

### Potential Enhancements
- **Station Meta**: Could add more location data (address, facilities, etc.)
- **Service Meta**: Could add more service details (capacity, amenities, etc.)
- **Calendar Enhancements**: Could add time-based exceptions (runs only morning/afternoon)
- **Caching**: Transient caching for expensive queries (station lists, service lookups)

---

## 10. Database Schema Diagram

```
wp_posts (mrt_station)
    ├─ ID (PK)
    ├─ post_title
    └─ post_type = 'mrt_station'
        └─ wp_postmeta
            ├─ mrt_station_type
            ├─ mrt_lat
            ├─ mrt_lng
            └─ mrt_display_order

wp_posts (mrt_service)
    ├─ ID (PK)
    ├─ post_title
    └─ post_type = 'mrt_service'
        └─ wp_postmeta
            └─ mrt_direction
        └─ wp_term_relationships
            └─ mrt_train_type (taxonomy)

{prefix}_mrt_stoptimes
    ├─ id (PK)
    ├─ service_post_id (FK → wp_posts.ID)
    ├─ station_post_id (FK → wp_posts.ID)
    ├─ stop_sequence
    ├─ arrival_time
    ├─ departure_time
    ├─ pickup_allowed
    └─ dropoff_allowed

{prefix}_mrt_calendar
    ├─ id (PK)
    ├─ service_post_id (FK → wp_posts.ID)
    ├─ start_date
    ├─ end_date
    ├─ mon, tue, wed, thu, fri, sat, sun
    ├─ include_dates
    └─ exclude_dates

wp_terms (mrt_train_type)
    ├─ term_id (PK)
    ├─ name
    └─ slug
        └─ wp_term_relationships
            └─ object_id → wp_posts.ID (mrt_service)
```

---

*Last updated: Based on current codebase structure*

