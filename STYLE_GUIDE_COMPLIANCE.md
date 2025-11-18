# Style Guide Compliance Report

This document lists changes needed to align the project with the WordPress Plugin Style Guide.

## 🔴 Critical Issues

### 1. Asset Loading (Performance)
**Issue**: CSS and JavaScript files are not being enqueued using WordPress functions.

**Current State**: No `wp_enqueue_style()` or `wp_enqueue_script()` calls found.

**Required Changes**:
- Add asset enqueuing in `inc/admin-page.php` or create a new file `inc/assets.php`
- Enqueue CSS only in admin area
- Enqueue JS only where needed
- Use plugin version for cache-busting

**Location**: `inc/admin-page.php` or new `inc/assets.php`

**Example**:
```php
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'mrt_') === false) return;
    wp_enqueue_style('mrt-admin', MRT_URL . 'assets/admin.css', [], MRT_VERSION);
    wp_enqueue_script('mrt-admin', MRT_URL . 'assets/admin.js', ['jquery'], MRT_VERSION, true);
});
```

### 2. SQL Injection Risk
**Issue**: In `inc/shortcode.php` line 52, column name is interpolated directly in SQL.

**Current Code**:
```php
$sql = $wpdb->prepare("SELECT service_post_id, include_dates, exclude_dates, $col AS dow
    FROM $calendar
    WHERE %s BETWEEN start_date AND end_date", $dateYmd);
```

**Required Change**: Column names should be whitelisted, not interpolated.

**Fix**:
```php
$allowed_cols = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
$col = in_array($col, $allowed_cols) ? $col : 'mon';
$sql = $wpdb->prepare("SELECT service_post_id, include_dates, exclude_dates, `$col` AS dow
    FROM $calendar
    WHERE %s BETWEEN start_date AND end_date", $dateYmd);
```

## 🟡 Important Improvements

### 3. Function Documentation
**Issue**: Functions lack PHPDoc comments.

**Required Changes**: Add PHPDoc blocks to all functions.

**Example**:
```php
/**
 * Get all stations ordered by display order
 *
 * @return array Array of station post IDs
 */
function MRT_get_all_stations() {
    // ...
}
```

**Files to update**:
- `inc/shortcode.php` - All functions
- `inc/import.php` - All functions
- `inc/admin-page.php` - All functions
- `inc/admin-list.php` - All functions
- `museum-railway-timetable.php` - MRT_activate(), MRT_deactivate()

### 4. Escaping Improvements
**Issue**: Some places use `_e()` instead of `esc_html_e()`.

**Current Code** (line 135 in `inc/import.php`):
```php
<h1><?php _e('CSV Import', 'museum-railway-timetable'); ?></h1>
```

**Required Change**:
```php
<h1><?php esc_html_e('CSV Import', 'museum-railway-timetable'); ?></h1>
```

**Files to check**:
- `inc/import.php` - Line 135, 163
- `inc/admin-page.php` - Line 27, 69

### 5. Asset File Structure
**Issue**: CSS/JS files are directly in `assets/` instead of `assets/css/` and `assets/js/`.

**Current Structure**:
```
assets/
├─ admin.css
└─ admin.js
```

**Recommended Structure** (per style guide):
```
assets/
├─ css/
│   └─ admin.css
└─ js/
    └─ admin.js
```

**Note**: This is optional but recommended for better organization.

### 6. Missing README
**Issue**: No README.md file exists.

**Required**: Create `README.md` with:
- Plugin description
- Installation instructions
- Usage examples
- Development setup
- Contributing guidelines

## 🟢 Nice to Have

### 7. Caching for Expensive Queries
**Issue**: No caching implemented for database queries.

**Recommendation**: Add transient caching for:
- `MRT_get_all_stations()` - Cache station list
- `MRT_services_running_on_date()` - Cache service lookups

**Example**:
```php
function MRT_get_all_stations() {
    $cache_key = 'mrt_all_stations';
    $stations = get_transient($cache_key);
    if (false === $stations) {
        $q = new WP_Query([...]);
        $stations = $q->posts;
        set_transient($cache_key, $stations, HOUR_IN_SECONDS);
    }
    return $stations;
}
```

### 8. Code Comments
**Issue**: Some complex logic lacks explanatory comments.

**Files to improve**:
- `inc/shortcode.php` - `MRT_services_running_on_date()` function
- `inc/import.php` - CSV parsing logic

### 9. Error Handling
**Issue**: Limited error handling in some functions.

**Recommendation**: Add error handling for:
- Database operations
- File operations (if any)
- User input validation

## ✅ What's Already Good

- ✅ ABSPATH checks in all PHP files
- ✅ Consistent function naming (MRT_ prefix)
- ✅ Proper use of `$wpdb->prepare()` in most places
- ✅ Good use of sanitization functions
- ✅ Proper escaping in most output
- ✅ Consistent text domain usage
- ✅ Proper nonce usage in forms
- ✅ Capability checks in admin functions
- ✅ CSS class naming follows convention (.mrt-*)
- ✅ JavaScript wrapped in IIFE

## 📋 Priority Action Items

1. **High Priority**:
   - [ ] Add asset enqueuing (wp_enqueue_style/wp_enqueue_script)
   - [ ] Fix SQL injection risk in shortcode.php
   - [ ] Replace `_e()` with `esc_html_e()` where needed

2. **Medium Priority**:
   - [ ] Add PHPDoc to all functions
   - [ ] Create README.md
   - [ ] Reorganize assets folder structure (optional)

3. **Low Priority**:
   - [ ] Add caching for expensive queries
   - [ ] Improve code comments
   - [ ] Enhance error handling

## 📝 Notes

- The project structure already matches the style guide (using `inc/` folder)
- Most security practices are already in place
- The main gaps are documentation and asset loading

