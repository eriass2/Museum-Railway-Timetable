# Developer Guide

## Kodkvalitet (Linting)

### Krav
- PHP 8.0+
- [Composer](https://getcomposer.org/)

### Installation

```bash
composer install
```

### Köra lint

```bash
# PHPStan (statisk analys)
composer phpstan

# PHPCS (kodstil)
composer phpcs

# Båda
composer lint
```

Windows (PowerShell):
```powershell
.\scripts\lint.ps1
```

### Pre-commit hooks

```bash
pip install pre-commit
pre-commit install
```

Kör manuellt: `pre-commit run --all-files`

## Konstanter

- **MRT_TEXT_DOMAIN** – Text domain för översättningar
- **MRT_POST_TYPE_STATION**, **MRT_POST_TYPE_ROUTE**, **MRT_POST_TYPE_TIMETABLE**, **MRT_POST_TYPE_SERVICE**
- **MRT_TAXONOMY_TRAIN_TYPE**
- **MRT_POST_TYPES** – Array med alla post types

## Typning

Filer använder `declare(strict_types=1)` och type hints där det är möjligt.
