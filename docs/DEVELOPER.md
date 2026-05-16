# Developer Guide – Museum Railway Timetable

En ingång för utvecklare. Läs detta först.

---

## Snabbstart

```powershell
git clone <repo>
cd Museum-Railway-Timetable
composer install

copy local\deploy.config.example.json local\deploy.config.json
# Redigera localPath / localUrl

.\local\deploy.ps1 -OpenBrowser

composer plugin-check    # obligatoriskt före commit
# valfritt: composer lint
```

---

## Dokumentation

| Dokument | Innehåll |
|----------|----------|
| [README.md](README.md) | Index |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | PHP, CSS, JS, `.mrt-*` |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Lager, testning, `inc/`-struktur |
| [DATA_MODEL.md](DATA_MODEL.md) | Post types, relationer |
| [SHORTCODES.md](SHORTCODES.md) | Alla shortcodes |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | WCAG + release-rökning |
| [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) | Skapa tidtabell i admin |
| [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md) | PHP/Composer på Windows |
| [assets/CSS_STRUCTURE.md](../assets/CSS_STRUCTURE.md) | CSS-moduler |

---

## Kommandon och CI

| Kommando | Vad det gör |
|----------|-------------|
| `composer plugin-check` | `php scripts/validate.php` – filer, syntax, ABSPATH, text domain |
| `composer test` | PHPUnit |
| `composer phpstan` / `phpcs` / `lint` | Statisk analys + WPCS |

GitHub Actions (`.github/workflows/ci.yml`): validate, phpstan, test vid push/PR. Dependabot uppdaterar Composer månadsvis.

**PHPStan:** `phpstan-wordpress`, config `phpstan.neon`. **PHPCS:** WPCS; prefix `MRT_` kan flaggas – bedöm mot [STYLE_GUIDE.md](STYLE_GUIDE.md). `composer phpcbf` fixar formatering där det går.

**Pre-commit:** kräver bash (WSL/Git Bash på Windows) – `pre-commit install`.

---

## Checklista före deploy

- [ ] `composer plugin-check` och `composer test` gröna
- [ ] Manuellt i WordPress: stationer, rutter, tidtabell, shortcodes
- [ ] [ACCESSIBILITY.md](ACCESSIBILITY.md) – kort rökning vid UI-ändringar
- [ ] Nya strängar: `languages/*.po` → kompilera `.mo` (Poedit eller `msgfmt … -o …/sv_SE.mo`)
- [ ] `.\local\deploy.ps1` om du testar i Local

---

## Krav

- PHP 8.2+ (plugin), Composer, WordPress 6.0+
- Windows utan PHP: [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md)

---

## Konstanter (`inc/constants.php`)

`MRT_TEXT_DOMAIN`, `MRT_POST_TYPE_*`, `MRT_TAXONOMY_TRAIN_TYPE`, `MRT_POST_TYPES`.

PHP: `declare(strict_types=1)` där det passar.
