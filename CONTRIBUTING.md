# Contributing

Se **[docs/DEVELOPER.md](docs/DEVELOPER.md)** för snabbstart, kodstandarder och hur du kör `composer plugin-check` innan du öppnar en pull request.

Kort: följ [docs/design/STYLE_GUIDE.md](docs/design/STYLE_GUIDE.md) och [docs/domain/ARCHITECTURE.md](docs/domain/ARCHITECTURE.md) (lager, testning, logik vs UI), kör validering och testa manuellt i WordPress.

## Pull requests

Vid PR: använd checklistan i [`.github/pull_request_template.md`](.github/pull_request_template.md) (fylls i automatiskt på GitHub) och säkerställ att `composer test` samt `php scripts/validate.php` är gröna.
