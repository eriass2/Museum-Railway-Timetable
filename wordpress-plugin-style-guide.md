# 🧩 WordPress Plugin Style Guide

Best practices for structure, quality, performance, and scalability.

This Style Guide defines standards for how a WordPress plugin should be
developed, documented, and maintained. The purpose is to ensure
consistency, good quality, and long-term stability in projects with multiple
developers.

## 📁 Project Structure

### General Structure

    plugin-name/
    │
    ├─ plugin-name.php          # Main file with plugin header
    ├─ uninstall.php             # Uninstall hook
    ├─ readme.txt                # WordPress.org readme (optional)
    │
    ├─ inc/                      # PHP files (or includes/)
    │   ├─ admin-page.php
    │   ├─ admin-list.php
    │   ├─ cpt.php
    │   ├─ import.php
    │   └─ shortcode.php
    │
    ├─ assets/
    │   ├─ css/
    │   │   └─ admin.css
    │   ├─ js/
    │   │   └─ admin.js
    │   ├─ images/               # (optional)
    │   └─ dist/                 # (optional, for compiled assets)
    │
    └─ languages/                # Translation files
        ├─ plugin-name.pot
        └─ plugin-name-sv_SE.po

### Alternative Structure (more object-oriented)

    plugin-name/
    │
    ├─ plugin-name.php
    ├─ uninstall.php
    │
    ├─ includes/
    │   ├─ class-plugin.php
    │   ├─ class-loader.php
    │   ├─ class-activator.php
    │   ├─ class-deactivator.php
    │   ├─ admin/
    │   │   ├─ class-admin.php
    │   │   └─ class-admin-menu.php
    │   ├─ public/
    │   │   └─ class-public.php
    │   ├─ api/
    │   │   └─ class-rest-controller.php
    │   ├─ database/
    │   │   └─ class-schema.php
    │   └─ helpers/
    │       └─ functions-template.php
    │
    ├─ assets/
    └─ languages/

## 🧱 Coding Standards (PHP, JS, CSS)

### PHP

#### Naming Conventions

- **Functions**: Prefix with plugin prefix (e.g., `MRT_`), snake_case
  ```php
  function MRT_render_admin_page() { }
  function MRT_get_all_stations() { }
  ```

- **Classes**: PascalCase, prefix with plugin prefix
  ```php
  class MRT_Admin_Menu { }
  ```

- **Variables**: snake_case
  ```php
  $station_id = 0;
  $service_ids = [];
  ```

- **Constants**: UPPERCASE with underscores, prefix
  ```php
  define('MRT_VERSION', '0.1.0');
  define('MRT_PATH', plugin_dir_path(__FILE__));
  ```

- **Hooks**: Use plugin prefix for filters/actions
  ```php
  add_action('init', 'mrt_register_post_types');
  apply_filters('mrt_overview_days_ahead', 60);
  ```

#### Code Structure

- **Security**: Always check `ABSPATH` in every file
  ```php
  if (!defined('ABSPATH')) { exit; }
  ```

- **Sanitization**: Sanitize all user input
  ```php
  $input = sanitize_text_field($_POST['field']);
  $id = intval($_GET['id']);
  $slug = sanitize_title($string);
  ```

- **Escaping**: Escape all output
  ```php
  echo esc_html($variable);
  echo esc_attr($attribute);
  echo esc_url($url);
  ```

- **Internationalization**: Use `__()`, `_e()`, `esc_html__()` etc.
  ```php
  __('Text to translate', 'text-domain');
  esc_html__('Safe HTML text', 'text-domain');
  ```

- **Hooks**: Use closures for simple hooks, functions for complex ones
  ```php
  add_action('admin_menu', function () {
      add_menu_page(...);
  });
  ```

- **Database**: Use `$wpdb->prepare()` for queries
  ```php
  $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id);
  ```

#### Comments

- **File headers**: PHPDoc block in main file
- **Functions**: Describe what the function does
- **Complex logic**: Explain why, not just what

### JavaScript

#### Naming Conventions

- **Variables**: camelCase
  ```javascript
  const stationId = 123;
  let serviceList = [];
  ```

- **Functions**: camelCase
  ```javascript
  function renderTimetable() { }
  ```

- **Constants**: UPPERCASE
  ```javascript
  const MAX_RESULTS = 10;
  ```

#### Code Structure

- **jQuery**: Wrap in IIFE to avoid conflicts
  ```javascript
  (function($) {
      $(function() {
          // Code here
      });
  })(jQuery);
  ```

- **Event handlers**: Use `.on()` for delegated events when possible
- **AJAX**: Use WordPress AJAX API with nonces

### CSS

#### Naming Conventions

- **Classes**: Prefix with plugin prefix, kebab-case
  ```css
  .mrt-timetable { }
  .mrt-month-table { }
  .mrt-daynum { }
  ```

- **BEM-like structure**: `.mrt-component`, `.mrt-component-element`, `.mrt-component--modifier`

#### Code Structure

- **Organization**: Group related rules
- **Specificity**: Avoid unnecessarily high specificity
- **Responsive**: Use media queries for mobile/tablet

## 🔒 Security

### Input Validation & Sanitization

- **Always sanitize input**: Use `sanitize_text_field()`, `sanitize_email()`, `intval()`, etc.
- **Nonces**: Use nonces for all forms and AJAX requests
  ```php
  wp_nonce_field('action_name', 'nonce_name');
  wp_verify_nonce($_POST['nonce_name'], 'action_name');
  ```

- **Capability checks**: Check user permissions
  ```php
  if (!current_user_can('manage_options')) { return; }
  ```

### SQL Injection Prevention

- **Always use `$wpdb->prepare()`**: Never use direct string interpolation in SQL
  ```php
  // ❌ WRONG
  $wpdb->query("SELECT * FROM table WHERE id = $id");
  
  // ✅ CORRECT
  $wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);
  ```

### XSS Prevention

- **Escape all output**: Use `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()`
- **JSON**: Use `wp_json_encode()` for JSON output

### File Access

- **Check ABSPATH**: In every PHP file
- **Direct file access**: Prevent direct access to files

## 🌍 Internationalization (i18n)

### Text Domain

- **Consistent text domain**: Use the same text domain throughout the plugin
  ```php
  __('Text', 'museum-railway-timetable');
  ```

### Functions

- **Translatable text**: Use WordPress i18n functions
  - `__()` - Returns translated text
  - `_e()` - Echo translated text
  - `esc_html__()` - Escape and translate
  - `esc_html_e()` - Escape, translate and echo
  - `_n()` - Plural forms
  - `_x()` - Context-aware translation

### Examples

```php
// Simple translation
echo __('Hello World', 'text-domain');

// With escaping
echo esc_html__('Safe HTML', 'text-domain');

// Plural
echo _n('One item', '%d items', $count, 'text-domain');

// With context
echo _x('Post', 'verb', 'text-domain');
```

## 🗄️ Database

### Table Names

- **Prefix**: Use `$wpdb->prefix` for all custom tables
  ```php
  $table = $wpdb->prefix . 'mrt_stoptimes';
  ```

### Schema Management

- **dbDelta()**: Use for creating/updating tables
  ```php
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  dbDelta($sql);
  ```

### Queries

- **Prepared statements**: Always use `$wpdb->prepare()`
- **Direct queries**: Use `$wpdb->get_results()`, `$wpdb->get_row()`, `$wpdb->get_var()`
- **Insert/Update**: Use `$wpdb->insert()`, `$wpdb->update()`, `$wpdb->delete()`

## 📝 Documentation

### File Headers

- **Plugin header**: In main file
  ```php
  /**
   * Plugin Name: Plugin Name
   * Description: Plugin description
   * Version: 1.0.0
   * Author: Author Name
   * Text Domain: text-domain
   */
  ```

### Function Documentation

- **PHPDoc**: Document functions with @param, @return
  ```php
  /**
   * Get all stations ordered by display order
   *
   * @return array Array of station post IDs
   */
  function MRT_get_all_stations() { }
  ```

### Inline Comments

- **Complex logic**: Explain why, not just what
- **TODO/FIXME**: Mark temporary code or known issues

## 🎨 File Organization

### Project Structure (for this project)

```
museum-railway-timetable/
│
├─ museum-railway-timetable.php  # Main file
├─ uninstall.php                 # Uninstall hook
│
├─ inc/                          # PHP files (not includes/)
│   ├─ admin-page.php
│   ├─ admin-list.php
│   ├─ cpt.php
│   ├─ import.php
│   └─ shortcode.php
│
├─ assets/
│   ├─ admin.css
│   └─ admin.js
│
└─ languages/                    # Translation files
    ├─ museum-railway-timetable.pot
    └─ museum-railway-timetable-sv_SE.po
```

### File Names

- **PHP files**: lowercase, hyphens for separation (e.g., `admin-page.php`)
- **CSS/JS**: lowercase, hyphens (e.g., `admin.css`)

## 🔄 Version Control

### Git Best Practices

- **Commit messages**: Descriptive, in English
- **Branching**: Use branches for features/fixes
- **.gitignore**: Include WordPress core, node_modules, etc.

### Exclude from Git

- WordPress core files
- node_modules/
- .DS_Store
- IDE files (.idea/, .vscode/)
- Log files (*.log)

## ⚡ Performance

### Database Queries

- **Caching**: Use transients for expensive queries
  ```php
  $data = get_transient('mrt_stations_cache');
  if (false === $data) {
      $data = expensive_query();
      set_transient('mrt_stations_cache', $data, HOUR_IN_SECONDS);
  }
  ```

- **Query optimization**: Use proper indexes, avoid N+1 queries
- **WP_Query**: Use `fields => 'ids'` when possible

### Asset Loading

- **Conditional loading**: Load CSS/JS only where needed
  ```php
  if (is_admin()) {
      wp_enqueue_style('mrt-admin', MRT_URL . 'assets/admin.css');
  }
  ```

- **Versioning**: Use plugin version for cache-busting
  ```php
  wp_enqueue_script('mrt-admin', MRT_URL . 'assets/admin.js', [], MRT_VERSION, true);
  ```

## 🧪 Testing

### Code Quality

- **PHP_CodeSniffer**: Use WordPress Coding Standards
- **Linting**: Check JavaScript/CSS for errors

### Functional Testing

- **Manual testing**: Test all features in different scenarios
- **Edge cases**: Test with empty values, invalid input, etc.

## 📋 Checklist for New Code

- [ ] Security: ABSPATH check, sanitization, escaping, nonces
- [ ] Internationalization: All text translatable
- [ ] Documentation: Comments where necessary
- [ ] Performance: Efficient queries, caching where appropriate
- [ ] Coding standards: Follows naming and structure
- [ ] Testing: Tested in different scenarios
