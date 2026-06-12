# Maintenance scripts (one-off refactors)

PHP helpers used during large file splits and similar refactors. **Not** part of daily dev gates.

Run from repo root when needed, e.g.:

```bash
php scripts/maintenance/split-routes.php
```

Shared helper: [../lib/extract-php-functions.php](../lib/extract-php-functions.php).
