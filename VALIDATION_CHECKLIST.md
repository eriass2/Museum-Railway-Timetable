# Validation Checklist for Museum Railway Timetable

Run this checklist before deploying the plugin.

## ✅ File Structure

- [x] Main plugin file exists: `museum-railway-timetable.php`
- [x] Uninstall file exists: `uninstall.php`
- [x] All required PHP files exist in `inc/` directory
- [x] CSS file exists: `assets/admin.css`
- [x] JavaScript file exists: `assets/admin.js`
- [x] Translation files exist: `languages/museum-railway-timetable.pot` and `languages/museum-railway-timetable-sv_SE.po`

## ✅ Security Checks

- [x] All PHP files have ABSPATH check (except uninstall.php which has WP_UNINSTALL_PLUGIN)
- [x] All user input is sanitized
- [x] All output is escaped
- [x] Nonces are used for forms and AJAX
- [x] Capability checks are in place for admin functions
- [x] SQL queries use `$wpdb->prepare()`

## ✅ Code Quality

- [x] No inline styles in PHP files
- [x] No syntax errors (run `php -l` on all files)
- [x] All functions have PHPDoc comments
- [x] Consistent naming conventions (MRT_ prefix for functions)
- [x] Text domain is consistent: `museum-railway-timetable`

## ✅ WordPress Standards

- [x] Plugin header is complete (Name, Description, Version, Text Domain, etc.)
- [x] Assets are enqueued properly using `wp_enqueue_style()` and `wp_enqueue_script()`
- [x] Translation functions are used (`__()`, `esc_html__()`, etc.)
- [x] Hooks use plugin prefix (`mrt_`)

## ✅ CSS/JavaScript

- [x] CSS follows naming convention (`.mrt-*`)
- [x] JavaScript uses IIFE with jQuery
- [x] No console.log in production code (only with debug flag)
- [x] Responsive design implemented

## ✅ Translation Files

- [x] All translatable strings are in `.pot` file
- [x] Swedish translation file is up to date
- [x] No missing translations

## ✅ Functionality

- [x] Plugin activates without errors
- [x] Plugin deactivates without errors
- [x] Shortcodes work correctly
- [x] Admin pages load correctly
- [x] CSV import works
- [x] Database tables are created correctly

## Manual Testing Checklist

Before deploying, test:

1. **Activation**
   - [ ] Activate plugin - no errors
   - [ ] Check database tables are created
   - [ ] Check default options are set

2. **Admin Interface**
   - [ ] Settings page loads
   - [ ] Stations overview page loads
   - [ ] CSV import page loads
   - [ ] All forms submit correctly
   - [ ] Nonces work correctly

3. **Shortcodes**
   - [ ] `[museum_timetable]` displays correctly
   - [ ] `[museum_timetable_picker]` displays correctly
   - [ ] `[museum_timetable_month]` displays correctly
   - [ ] All parameters work as expected

4. **CSV Import**
   - [ ] Stations import works
   - [ ] Stop times import works
   - [ ] Calendar import works
   - [ ] Error handling works for invalid CSV

5. **Frontend**
   - [ ] CSS loads correctly
   - [ ] Responsive design works on mobile
   - [ ] No JavaScript errors in console
   - [ ] All text is translatable

6. **Deactivation/Uninstall**
   - [ ] Deactivation doesn't remove data
   - [ ] Uninstall removes data (if tested)

## Pre-Deployment Steps

1. Run validation script: `php validate.php` (if PHP CLI available)
2. Check all items in this checklist
3. Test in a clean WordPress installation
4. Check browser console for JavaScript errors
5. Test with different WordPress versions (if applicable)
6. Test with different PHP versions (if applicable)

## Known Issues

None currently.

## Notes

- Plugin requires WordPress 6.0+
- Plugin requires PHP 8.0+
- All code follows WordPress coding standards
- DRY principle applied throughout
- No inline styles - all in CSS file
